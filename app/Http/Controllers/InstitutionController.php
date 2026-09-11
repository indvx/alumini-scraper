<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\LocationSearch;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstitutionController extends Controller
{
    public function __construct(
        protected InstitutionRepositoryInterface $institutionRepository,
        protected LocationSearchRepositoryInterface $searchRepository
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'postcode' => $request->input('postcode'),
            'search_id' => $request->input('search_id'),
        ];

        $institutions = $this->institutionRepository->getList($filters, 15);

        return view('institutions.search', [
            'institutions' => $institutions,
            'filters' => $filters,
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

    public function edit(Institution $institution)
    {
        return view('institutions.edit', [
            'institution' => $institution,
        ]);
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:school,college,university,kindergarten,other',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $this->institutionRepository->update($institution, $validated);

        return redirect()
            ->route('institutions.show', $institution)
            ->with('success', "Institution '{$institution->name}' updated successfully.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'postcode' => $request->input('postcode'),
            'search_id' => $request->input('search_id'),
        ];

        $institutions = $this->institutionRepository->getList($filters, 0, true);

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
