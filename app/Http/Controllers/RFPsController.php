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
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($institutionId && $institutionId !== 'all') {
            $query->where('institution_id', $institutionId);
        }

        if ($platformId && $platformId !== 'all') {
            $query->where('rfps_platform_id', $platformId);
        }

        $rfps = $query->latest('date_open')->latest('id')->paginate(15)->withQueryString();

        $institutions = Institution::query()
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
            'openCount' => RFP::where('status', 'open')->count(),
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
            'institution_name' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:open,past,all',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $institutionName = trim((string) ($validated['institution_name'] && $validated['institution_name'] != 'all' ? $validated['institution_name'] : ''));
        $platformName = trim((string) ($validated['platform_name'] && $validated['platform_name'] != 'all' ? $validated['platform_name'] : ''));
        $type = strtolower($validated['type'] ?? 'open');

        try {
            // Case 1: Specific institution specified -> execute rfp:scrape-institution command
            if (!empty($institutionName)) {
                $params = [
                    'university' => $institutionName,
                    '--platform' => !empty($platformName) ? $platformName : 'Bonfire',
                    '--type' => $type,
                ];

                if (!empty($validated['from_date'])) {
                    $params['--from'] = $validated['from_date'];
                }

                if (!empty($validated['to_date'])) {
                    $params['--to'] = $validated['to_date'];
                }

                $exitCode = Artisan::call('rfp:scrape-institution', $params);
                $target = "'{$institutionName}' on platform '" . ($params['--platform']) . "'";
            }
            // Case 2: Platform specified without specific institution -> execute rfp:scrape-platform command
            elseif (!empty($platformName)) {
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
