<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\RFP;
use App\Models\RFPsPlatform;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RFPsController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', 'all'));
        $institutionId = $request->input('institution_id');
        $platformId = $request->input('rfps_platform_id');

        $query = RFP::query()
            ->with(['institution', 'platform']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('project_id', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('institution', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('platform', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '' && $status !== 'all') {
            $statusLower = strtolower($status);
            if ($statusLower === 'open') {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['open', 'active']);
            } elseif ($statusLower === 'past' || $statusLower === 'closed') {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['past', 'closed', 'evaluation', 'complete', 'completed']);
            } elseif ($statusLower === 'awarded') {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['awarded', 'award']);
            } elseif ($statusLower === 'cancelled' || $statusLower === 'canceled') {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['cancelled', 'canceled']);
            } else {
                $query->where(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), $statusLower);
            }
        }

        if ($institutionId && $institutionId !== 'all') {
            $query->where('institution_id', $institutionId);
        }

        if ($platformId && $platformId !== 'all') {
            $query->where('rfps_platform_id', $platformId);
        }

        // Filtered counts calculation
        $filteredQuery = clone $query;
        $filteredCount = $filteredQuery->count();
        $filteredOpenCount = (clone $query)->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['open', 'active'])->count();

        $rfps = $query->latest('date_open')->latest('id')->paginate(15)->withQueryString();

        $institutions = Institution::query()
            ->where(function ($q) {
                $q->whereHas('rfpPlatforms')
                    ->orWhereHas('rfps');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $platforms = RFPsPlatform::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('rfps.index', [
            'rfps' => $rfps,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'institution_id' => $institutionId,
                'rfps_platform_id' => $platformId,
            ],
            'institutions' => $institutions,
            'platforms' => $platforms,
            'totalCount' => RFP::count(),
            'openCount' => RFP::whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['open', 'active'])->count(),
            'filteredCount' => $filteredCount,
            'filteredOpenCount' => $filteredOpenCount,
        ]);
    }

    public function show(RFP $rfp): View
    {
        $rfp->load(['institution', 'platform']);

        return view('rfps.show', [
            'rfp' => $rfp,
        ]);
    }

    public function scrape(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => 'nullable',
            'institution_name' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:open,past,all',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $institutionId = $validated['institution_id'] ?? null;
        $institutionInput = $validated['institution_name'] ?? '';
        $platformInput = $validated['platform_name'] ?? '';

        $institutionName = '';
        if (! empty($institutionId) && $institutionId !== 'all') {
            $inst = Institution::find($institutionId);
            if ($inst) {
                $institutionName = $inst->name;
            }
        }

        if (empty($institutionName) && ! empty($institutionInput) && $institutionInput !== 'all') {
            $institutionName = trim($institutionInput);
        }

        $platformName = trim((string) ($platformInput !== 'all' ? $platformInput : ''));
        $type = strtolower($validated['type'] ?? 'open');

        try {
            // Case 1: Specific institution specified -> execute rfp:scrape-institution command
            if (! empty($institutionName)) {
                $platformToUse = ! empty($platformName) ? $platformName : 'Bonfire';
                $params = [
                    'university' => $institutionName,
                    '--platform' => $platformToUse,
                    '--type' => $type,
                ];

                if (! empty($validated['from_date'])) {
                    $params['--from'] = $validated['from_date'];
                }

                if (! empty($validated['to_date'])) {
                    $params['--to'] = $validated['to_date'];
                }

                $exitCode = Artisan::call('rfp:scrape-institution', $params);
                $target = "'{$institutionName}' on platform '{$platformToUse}'";
            }
            // Case 2: Platform specified without specific institution -> execute rfp:scrape-platform command
            elseif (! empty($platformName)) {
                $exitCode = Artisan::call('rfp:scrape-platform', [
                    'platform' => $platformName,
                    '--type' => $type,
                ]);
                $target = "platform '{$platformName}' across all linked institutions";
            }
            // Case 3: Bulk / All scrape -> execute rfp:scrape-all command
            else {
                $exitCode = Artisan::call('rfp:scrape-all', [
                    '--type' => $type,
                ]);
                $target = 'all registered institutions and platforms';
            }

            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                return redirect()->back()->with('error', 'Scraping failed: ' . ($output ?: 'Command returned a non-zero exit status.'));
            }

            return redirect()
                ->route('rfps.index')
                ->with('success', "Scrape command executed successfully for {$target}!");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Scraping error: {$e->getMessage()}");
        }
    }
}
