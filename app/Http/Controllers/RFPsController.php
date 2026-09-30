<?php

namespace App\Http\Controllers;

use App\Enums\Rfp\RfpStatus;
use App\Models\Institution;
use App\Models\RFP;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RFPsController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $userKeyword = $request->has('use_keywords')
            ? boolval($request->input('use_keywords'))
            : (! $request->query());
        $rawKeywords = $request->input('keywords', []);
        $selectedKeywords = is_array($rawKeywords) ? array_values(array_filter(array_map('trim', $rawKeywords))) : [];
        $status = trim((string) $request->input('status'));
        $institutionId = $request->input('institution_id');
        $platformId = $request->input('rfps_platform_id');

        if (empty($search) && empty($status) && empty($institutionId) && empty($platformId)) {
            $userKeyword = true;
            $selectedKeywords = [
                'Alumni',
                'Engagement',
                'Event',
                'Campaign',
                'Community',
                'Network',
                'Alumnae',
                'Alumnus',
                'Alumna',
                'Software',
                'Social',
                'Digital',
                'Member',
                'Membership',
                'Mentor',
                'Mentorship',
                'Career',
                'Fundraising',
                'Donor',
                'Constituent',
                'Giving',
                'CRM',
                'Salesforce',
                'Blackbaud',
                'Raisers Edge',
                'Directory',
                'Associations',
                'Association',
                'Program',
                'Platform',
                'Technology Modernization',
                'System Migration',
                'Networking',
                'Advancement',
                'Volunteer',
                'Reunion',
                'Chapter',
                'Portal',
                'Migration',
                'Replacement',
                'Renewal',
                'Procurement',
                'Solicitation',
                'HiveBrite',
                'Almabase',
                'PeopleGrove',
                'Graduway',
                'Gravyty',
                'EnterpriseAlumni',
                'Toucantech',
            ];
        }

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

        if ($userKeyword && ! empty($selectedKeywords)) {
            $query->where(function ($q) use ($selectedKeywords) {
                foreach ($selectedKeywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%")
                        ->orWhere('description', 'like', "%{$kw}%")
                        ->orWhere('project_id', 'like', "%{$kw}%")
                        ->orWhere('reference_id', 'like', "%{$kw}%")
                        ->orWhere('department', 'like', "%{$kw}%");
                }
            });
        }

        if ($status !== '' && $status !== 'all') {
            $statusLower = strtolower($status);
            if ($statusLower === 'open') {
                $query->whereIn(DB::raw('LOWER(status)'), ['open', 'active']);
            } elseif ($statusLower === 'past' || $statusLower === 'closed') {
                $query->whereIn(DB::raw('LOWER(status)'), ['past', 'closed', 'evaluation', 'complete', 'completed']);
            } elseif ($statusLower === 'awarded') {
                $query->whereIn(DB::raw('LOWER(status)'), ['awarded', 'award']);
            } elseif ($statusLower === 'cancelled' || $statusLower === 'canceled') {
                $query->whereIn(DB::raw('LOWER(status)'), ['cancelled', 'canceled']);
            } else {
                $query->where(DB::raw('LOWER(status)'), $statusLower);
            }
        }

        if ($institutionId && $institutionId !== 'all') {
            $query->where('institution_id', $institutionId);
        }

        if ($platformId && $platformId !== 'all') {
            $query->where('rfps_platform_id', $platformId);
        }

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

        $filters = [
            'search' => $search,
            'use_keywords' => $userKeyword,
            'keywords' => $userKeyword ? $selectedKeywords : [],
            'status' => $status,
            'institution_id' => $institutionId,
            'rfps_platform_id' => $platformId,
        ];

        return view('rfps.index', [
            'rfps' => $rfps,
            'filters' => $filters,
            'institutions' => $institutions,
            'platforms' => $platforms,
        ]);
    }

    public function scrape(Request $request, RfpScraperManager $scraperManager): RedirectResponse
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
        $type = strtolower($validated['type'] ?? 'open');
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $statusEnum = match ($type) {
            'past', 'closed' => RfpStatus::PAST,
            'all' => RfpStatus::ALL,
            default => RfpStatus::OPEN,
        };

        $institutionName = '';
        $institutionObj = null;

        if (! empty($institutionId) && $institutionId !== 'all') {
            $inst = Institution::find($institutionId);
            if ($inst) {
                $institutionName = $inst->name;
                $institutionObj = $inst;
            }
        }

        if (empty($institutionName) && ! empty($institutionInput) && $institutionInput !== 'all') {
            $institutionName = trim($institutionInput);
            $institutionObj = Institution::where('name', 'like', "%{$institutionName}%")->first();
        }

        $platformName = trim((string) ($platformInput !== 'all' ? $platformInput : ''));

        try {
            $totalSaved = 0;
            $successRuns = 0;
            $failedRuns = 0;

            // Case Specific institution specified
            if (! empty($institutionName)) {
                $platformToUse = ! empty($platformName) ? $platformName : 'Bonfire';
                $platformRecord = RFPsPlatform::where('name', 'like', "%{$platformToUse}%")
                    ->orWhere('platform_type', 'like', "%{$platformToUse}%")
                    ->first();

                $result = $scraperManager->scrapeUniversity(
                    universityName: $institutionName,
                    platform: $platformToUse,
                    status: $statusEnum,
                    fromDate: $fromDate,
                    toDate: $toDate,
                    persist: true,
                    institution: $institutionObj,
                    platformRecord: $platformRecord
                );

                if ($result->isFailure()) {
                    return redirect()->back()->with('error', "Scraping failed for '{$institutionName}': {$result->errorMessage}");
                }

                $totalSaved = $result->count();

                return redirect()
                    ->route('rfps.index')
                    ->with('success', "Scraped {$totalSaved} RFP(s) successfully for '{$institutionName}' on platform '{$platformToUse}'!");
            }

            // Case Platform specified without specific institution
            elseif (! empty($platformName)) {
                $platforms = RFPsPlatform::where('platform_type', 'like', "%{$platformName}%")
                    ->orWhere('name', 'like', "%{$platformName}%")
                    ->with('rfpInstitutions')
                    ->get();

                if ($platforms->isEmpty()) {
                    return redirect()->back()->with('error', "No platform records found matching '{$platformName}'.");
                }

                foreach ($platforms as $pRecord) {
                    $instList = $pRecord->rfpInstitutions;
                    if ($instList->isEmpty()) {
                        $instList = Institution::all();
                    }

                    foreach ($instList as $inst) {
                        $result = $scraperManager->scrapeUniversity(
                            universityName: $inst->name,
                            platform: $pRecord->name ?? $platformName,
                            status: $statusEnum,
                            fromDate: $fromDate,
                            toDate: $toDate,
                            persist: true,
                            institution: $inst,
                            platformRecord: $pRecord
                        );

                        if ($result->isSuccess()) {
                            $successRuns++;
                            $totalSaved += $result->count();
                        } else {
                            $failedRuns++;
                        }
                    }
                }

                return redirect()
                    ->route('rfps.index')
                    ->with('success', "Platform scrape completed for '{$platformName}': {$totalSaved} RFP(s) saved across {$successRuns} institution(s).");
            }

            // Case Bulk / All scrape
            else {
                $institutions = Institution::with('rfpPlatforms')->get();

                if ($institutions->isEmpty()) {
                    return redirect()->back()->with('error', 'No institutions found in database.');
                }

                foreach ($institutions as $inst) {
                    $platforms = $inst->rfpPlatforms;
                    if ($platforms->isEmpty()) {
                        $platforms = RFPsPlatform::query();
                        if (! empty($inst->country)) {
                            $platforms->byCountry($inst->country);
                        }
                        $platforms = $platforms->get();
                    }

                    if ($platforms->isEmpty()) {
                        $platforms = RFPsPlatform::where('name', 'Bonfire')->get();
                    }

                    foreach ($platforms as $pRecord) {
                        $result = $scraperManager->scrapeUniversity(
                            universityName: $inst->name,
                            platform: $pRecord->name ?? 'Bonfire',
                            status: $statusEnum,
                            fromDate: $fromDate,
                            toDate: $toDate,
                            persist: true,
                            institution: $inst,
                            platformRecord: $pRecord
                        );

                        if ($result->isSuccess()) {
                            $successRuns++;
                            $totalSaved += $result->count();
                        } else {
                            $failedRuns++;
                        }
                    }
                }

                return redirect()
                    ->route('rfps.index')
                    ->with('success', "All-institution scrape completed: {$totalSaved} RFP(s) saved across {$successRuns} platform run(s).");
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Scraping error: {$e->getMessage()}");
        }
    }
}
