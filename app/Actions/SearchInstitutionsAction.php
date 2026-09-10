<?php

namespace App\Actions;

use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use App\Services\NominatimService;
use App\Services\OverpassService;
use Illuminate\Support\Facades\DB;

class SearchInstitutionsAction
{
    public function __construct(
        protected LocationSearchRepositoryInterface $searchRepository,
        protected InstitutionRepositoryInterface $institutionRepository,
        protected NominatimService $nominatimService,
        protected OverpassService $overpassService
    ) {}

    public function execute(string $query, bool $forceRefresh = false): array
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
        $parsedRecords = $this->overpassService->fetchSchoolsByArea($geo['area_id'], $geo['state'], $geo['country']);

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
