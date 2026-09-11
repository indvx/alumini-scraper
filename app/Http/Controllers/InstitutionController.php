<?php

namespace App\Http\Controllers;

use App\Actions\SearchInstitutionsAction;
use App\Models\Institution;
use App\Models\LocationSearch;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstitutionController extends Controller
{
    public function __construct(
        protected InstitutionRepositoryInterface $institutionRepository,
        protected LocationSearchRepositoryInterface $searchRepository
    ) {}

    public function search(Request $request, SearchInstitutionsAction $action)
    {
        $searchResult = null;
        $searchError = null;
        $locationQuery = $request->input('location_query');

        if ($locationQuery) {
            try {
                $searchResult = $action->execute(
                    query: $locationQuery,
                    forceRefresh: $request->boolean('force_refresh')
                );
            } catch (\Exception $e) {
                $searchError = $e->getMessage();
            }
        }

        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'postcode' => $request->input('postcode'),
            'search_id' => $request->input('search_id'),
        ];

        // dd($filters);
        // die;
        $institutions = $this->institutionRepository->getPaginated($filters, 15);
        $recentSearches = $this->searchRepository->getRecentSearches(10);
        $typeBreakdown = Institution::query()
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('institutions.search', [
            'institutions' => $institutions,
            'recentSearches' => $recentSearches,
            'typeBreakdown' => $typeBreakdown,
            'searchResult' => $searchResult,
            'searchError' => $searchError,
            'filters' => $filters,
            'locationQuery' => $locationQuery,
        ]);
    }

    public function show(Institution $institution)
    {
        $institution->update(['last_view' => now()]);
        $institution->refresh();
        $institution->load('search');

        $nearbyInstitutions = [];
        if ($institution->latitude && $institution->longitude) {
            $nearbyInstitutions = Institution::query()
                ->where('id', '!=', $institution->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select('*', DB::raw("(
                    6371 * acos(
                        cos(radians({$institution->latitude}))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians({$institution->longitude}))
                        + sin(radians({$institution->latitude}))
                        * sin(radians(latitude))
                    )
                ) AS distance"))
                ->orderBy('distance', 'asc')
                ->limit(5)
                ->get();
        }

        return view('institutions.show', [
            'institution' => $institution,
            'nearbyInstitutions' => $nearbyInstitutions,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'postcode' => $request->input('postcode'),
            'search_id' => $request->input('search_id'),
        ];

        $institutions = $this->institutionRepository->getFilteredList($filters);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="institutions_export.csv"',
        ];

        $callback = function () use ($institutions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Type', 'Latitude', 'Longitude', 'Address', 'City', 'State', 'Postcode', 'Phone', 'Website', 'OSM ID']);

            foreach ($institutions as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->name,
                    $s->type,
                    $s->latitude,
                    $s->longitude,
                    $s->address,
                    $s->city,
                    $s->state,
                    $s->postcode,
                    $s->phone,
                    $s->website,
                    $s->osm_id,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function dashboard()
    {
        $totalInstitutions = Institution::count();
        $totalSearches = LocationSearch::count();
        $recentSearches = LocationSearch::latest('searched_at')->limit(5)->get();
        $recentInstitutions = Institution::latest('last_view')->limit(6)->get();

        $typeCounts = Institution::query()
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('dashboard', [
            'totalInstitutions' => $totalInstitutions,
            'totalSearches' => $totalSearches,
            'recentSearches' => $recentSearches,
            'recentInstitutions' => $recentInstitutions,
            'typeBreakdown' => $typeCounts,
        ]);
    }
}
