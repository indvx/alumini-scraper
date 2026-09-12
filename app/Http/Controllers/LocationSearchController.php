<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use App\Services\NominatimService;
use App\Services\OverpassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationSearchController extends Controller
{
    public function __construct(
        protected LocationSearchRepositoryInterface $searchRepository,
        protected InstitutionRepositoryInterface $institutionRepository,
        protected NominatimService $nominatimService,
        protected OverpassService $overpassService
    ) {}

    /**
     * Handle the location search & OpenStreetMap ingestion.
     */
    public function search(Request $request): RedirectResponse
    {
        $country = trim((string) $request->input('country', ''));
        $state = trim((string) $request->input('state', ''));
        $city = trim((string) $request->input('city', ''));

        $locationQuery = trim((string) $request->input('location_query', ''));

        if (empty($locationQuery)) {
            $parts = array_filter([$city, $state, $country]);
            $locationQuery = implode(', ', $parts);
        }

        $forceRefresh = $request->boolean('force_refresh');

        if (empty($locationQuery)) {
            return redirect()->back()->with([
                'searchError' => 'Please select a valid location (Country and State required).',
            ]);
        }

        try {
            $result = $this->performSearch($locationQuery, $forceRefresh);

            return redirect()->route('institutions.index')->with([
                'locationQuery' => $locationQuery,
                'country' => $country,
                'state' => $state,
                'city' => $city,
                'searchResult' => $result,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'locationQuery' => $locationQuery,
                'country' => $country,
                'state' => $state,
                'city' => $city,
                'searchError' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Execute search query against cache or OpenStreetMap / Overpass API.
     */
    protected function performSearch(string $query, bool $forceRefresh = false): array
    {
        $cleanQuery = trim($query);

        // 1. Check cache by query
        $existingSearch = $this->searchRepository->findByExactQuery($cleanQuery);

        if ($existingSearch && ! $forceRefresh) {
            return [
                'search' => $existingSearch,
                'institutions' => $existingSearch->institutions,
                'cached' => true,
            ];
        }

        // 2. Resolve area via Nominatim
        $geo = $this->nominatimService->resolveLocationArea($cleanQuery);

        if (! $existingSearch) {
            $existingSearch = $this->searchRepository->findByAreaOrDisplayName($geo['area_id'], $geo['display_name']);

            if ($existingSearch && ! $forceRefresh) {
                return [
                    'search' => $existingSearch,
                    'institutions' => $existingSearch->institutions,
                    'cached' => true,
                ];
            }
        }

        // 3. Query Overpass API
        $parsedRecords = $this->overpassService->fetchInstitutionsByArea($geo['area_id'], $geo['state'], $geo['country']);

        return DB::transaction(function () use ($existingSearch, $cleanQuery, $geo, $parsedRecords) {
            $searchData = [
                'query' => $cleanQuery,
                'display_name' => $geo['display_name'],
                'area_id' => $geo['area_id'],
                'place_name' => $geo['place_name'],
                'state' => $geo['state'],
                'country' => $geo['country'],
                'total_found' => count($parsedRecords),
                'searched_at' => now(),
            ];

            if ($existingSearch) {
                $searchEntry = $this->searchRepository->updateSearch($existingSearch, $searchData);
            } else {
                $searchEntry = $this->searchRepository->createSearch($searchData);
            }

            $institutions = [];
            foreach ($parsedRecords as $rec) {
                $institutions[] = $this->institutionRepository->upsertRecord($rec, $searchEntry->id);
            }

            return [
                'search' => $searchEntry,
                'institutions' => $institutions,
                'cached' => false,
            ];
        });
    }
}
