<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\LocationSearch;
use App\Models\RFPsPlatform;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use Illuminate\Http\JsonResponse;
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

    public function searchApi(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));
        $country = trim((string) $request->input('country', ''));
        $builder = Institution::query();

        if ($country !== '') {
            $builder->byCountry($country);
        }

        if ($query !== '') {
            $keywords = array_filter(explode(' ', $query));
            $builder->where(function ($sub) use ($keywords) {
                foreach ($keywords as $word) {
                    $sub->where('name', 'like', "%{$word}%");
                }
            });
        }

        $institutions = $builder->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'type', 'city', 'state', 'country']);

        return response()->json([
            'success' => true,
            'data' => $institutions,
        ]);
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'type' => $request->input('type'),
            'postcode' => $request->input('postcode'),
            'search_id' => $request->input('search_id'),
        ];

        $institutions = $this->institutionRepository->getPaginated($filters, 15);

        return view('institutions.search', [
            'institutions' => $institutions,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $searches = LocationSearch::query()->latest('searched_at')->limit(50)->get();

        return view('institutions.create', [
            'searches' => $searches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:school,college,university,kindergarten,other',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'search_id' => 'nullable|exists:location_searches,id',
        ]);

        $institution = $this->institutionRepository->create($validated);

        return redirect()
            ->route('institutions.show', $institution)
            ->with('success', "Institution '{$institution->name}' created successfully.");
    }

    public function show(Institution $institution)
    {
        $institution->update(['last_view' => now()]);
        $institution->refresh();
        $institution->load(['search', 'rfpPlatforms']);

        $initialRfpPlatforms = RFPsPlatform::query()
            ->byCountry($institution->country)
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        return view('institutions.show', [
            'institution' => $institution,
            'allRfpPlatforms' => $initialRfpPlatforms,
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
            'country' => 'nullable|string|max:255',
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

    public function destroy(Institution $institution): RedirectResponse
    {
        $name = $institution->name;
        $this->institutionRepository->delete($institution);

        return redirect()
            ->route('institutions.index')
            ->with('success', "Institution '{$name}' deleted successfully.");
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
            fputcsv($file, ['ID', 'Name', 'Type', 'Latitude', 'Longitude', 'Address', 'City', 'State', 'Country', 'Postcode', 'Phone', 'Website', 'OSM ID']);

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
                    $s->country,
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
