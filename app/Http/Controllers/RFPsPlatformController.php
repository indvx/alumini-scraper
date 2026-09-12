<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\RFPsPlatform;
use App\Repositories\Contracts\RFPsPlatformRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RFPsPlatformController extends Controller
{
    public function __construct(
        protected RFPsPlatformRepositoryInterface $platformRepository
    ) {}

    public function searchApi(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));
        $builder = RFPsPlatform::query();

        if ($query !== '') {
            $keywords = array_filter(explode(' ', $query));
            $builder->where(function ($sub) use ($keywords) {
                foreach ($keywords as $word) {
                    $sub->where(function ($w) use ($word) {
                        $w->where('name', 'like', "%{$word}%")
                            ->orWhere('domain', 'like', "%{$word}%");
                    });
                }
            });
        }

        $platforms = $builder->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'domain', 'platform_type']);

        return response()->json([
            'success' => true,
            'data' => $platforms,
        ]);
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'platform_type' => $request->input('platform_type'),
            'country' => $request->input('country'),
            'status' => $request->input('status'),
        ];

        $platforms = $this->platformRepository->getPaginated($filters, 10);

        $totalCount = RFPsPlatform::count();
        $activeCount = RFPsPlatform::where('status', 'active')->count();
        $publicCount = RFPsPlatform::where('is_public', true)->count();

        $platformTypes = RFPsPlatform::whereNotNull('platform_type')
            ->where('platform_type', '!=', '')
            ->distinct()
            ->pluck('platform_type');

        $countries = RFPsPlatform::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country');

        return view('rfps-platform.index', [
            'platforms' => $platforms,
            'filters' => $filters,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'publicCount' => $publicCount,
            'platformTypes' => $platformTypes,
            'countries' => $countries,
        ]);
    }

    public function create()
    {
        return view('rfps-platform.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'platform_type' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255',
            'coverage' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['is_public'] = $request->boolean('is_public');
        $validated['requires_login'] = $request->boolean('requires_login');

        $platform = $this->platformRepository->create($validated);

        return redirect()
            ->route('rfps-platform.index')
            ->with('success', "RFP Platform '{$platform->name}' created successfully.");
    }

    public function show(RFPsPlatform $rfpsPlatform)
    {
        $rfpsPlatform->load('institutions');
        $initialInstitutions = Institution::query()->orderBy('name', 'asc')->limit(10)->get();

        return view('rfps-platform.show', [
            'platform' => $rfpsPlatform,
            'allInstitutions' => $initialInstitutions,
        ]);
    }

    public function edit(RFPsPlatform $rfpsPlatform)
    {
        return view('rfps-platform.edit', [
            'platform' => $rfpsPlatform,
        ]);
    }

    public function update(Request $request, RFPsPlatform $rfpsPlatform): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'platform_type' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255',
            'coverage' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['is_public'] = $request->boolean('is_public');
        $validated['requires_login'] = $request->boolean('requires_login');

        $this->platformRepository->update($rfpsPlatform, $validated);

        return redirect()
            ->route('rfps-platform.show', $rfpsPlatform)
            ->with('success', "RFP Platform '{$rfpsPlatform->name}' updated successfully.");
    }

    public function destroy(RFPsPlatform $rfpsPlatform): RedirectResponse
    {
        $name = $rfpsPlatform->name;
        $this->platformRepository->delete($rfpsPlatform);

        return redirect()
            ->route('rfps-platform.index')
            ->with('success', "RFP Platform '{$name}' deleted successfully.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'platform_type' => $request->input('platform_type'),
            'country' => $request->input('country'),
            'status' => $request->input('status'),
        ];

        $platforms = $this->platformRepository->getFilteredList($filters);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rfps_platforms_export.csv"',
        ];

        $callback = function () use ($platforms) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Domain', 'URL', 'Platform Type', 'Country', 'State', 'City', 'Institution Type', 'Coverage', 'Is Public', 'Requires Login', 'Status', 'Last Checked', 'Created At']);

            foreach ($platforms as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->name,
                    $p->domain,
                    $p->url,
                    $p->platform_type,
                    $p->country,
                    $p->state,
                    $p->city,
                    $p->institution_type,
                    $p->coverage,
                    $p->is_public ? 'Yes' : 'No',
                    $p->requires_login ? 'Yes' : 'No',
                    $p->status,
                    $p->last_checked ? $p->last_checked->toIso8601String() : '',
                    $p->created_at ? $p->created_at->toIso8601String() : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
