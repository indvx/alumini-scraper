<?php

namespace App\Http\Controllers;

use App\Enums\Rfp\RfpStatus;
use App\Models\Institution;
use App\Models\RFP;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RFPsController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = trim((string) $request->input('status'));
        $institutionId = $request->input('institution_id');
        $platformId = $request->input('rfps_platform_id');
        $dateOpen = $request->input('date_open');
        $dateClose = $request->input('date_close');

        $keywordCondition = strtoupper(trim((string) ($request->input('keyword_condition') ?? $request->input('condition') ?? 'OR')));
        if (! in_array($keywordCondition, ['AND', 'OR'], true)) {
            $keywordCondition = 'OR';
        }

        $userKeyword = $request->has('use_keywords')
            ? boolval($request->input('use_keywords'))
            : (! $request->query());
        $rawKeywords = $request->input('keywords', []);
        $selectedKeywords = is_array($rawKeywords) ? array_values(array_filter(array_map('trim', $rawKeywords))) : [];

        if (empty($search) && empty($status) && empty($institutionId) && empty($platformId) && empty($dateOpen) && empty($dateClose) && ! $request->has('use_keywords')) {
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
            $query->where(function ($sub) use ($selectedKeywords, $keywordCondition) {
                if ($keywordCondition === 'AND') {
                    foreach ($selectedKeywords as $kw) {
                        $sub->where(function ($kq) use ($kw) {
                            $kq->where('title', 'like', "%{$kw}%")
                                ->orWhere('description', 'like', "%{$kw}%")
                                ->orWhere('project_id', 'like', "%{$kw}%")
                                ->orWhere('reference_id', 'like', "%{$kw}%")
                                ->orWhere('department', 'like', "%{$kw}%");
                        });
                    }
                } else {
                    foreach ($selectedKeywords as $kw) {
                        $sub->orWhere('title', 'like', "%{$kw}%")
                            ->orWhere('description', 'like', "%{$kw}%")
                            ->orWhere('project_id', 'like', "%{$kw}%")
                            ->orWhere('reference_id', 'like', "%{$kw}%")
                            ->orWhere('department', 'like', "%{$kw}%");
                    }
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

        if (! empty($dateOpen)) {
            $query->whereDate('date_open', '>=', $dateOpen);
        }

        if (! empty($dateClose)) {
            $query->whereDate('date_close', '<=', $dateClose);
        }

        $rfps = $query->latest('date_open')->latest('id')->paginate(15)->withQueryString();

        $institutions = Institution::query()
            ->with('search:id,country')
            ->where(function ($q) {
                $q->whereHas('rfpPlatforms')
                    ->orWhereHas('rfps');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'state', 'country']);

        $countries = Institution::query()
            ->whereNotNull('country')
            ->whereRaw("TRIM(country) <> ''")
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->values();

        $statesByCountry = Institution::query()
            ->whereNotNull('country')
            ->whereRaw("TRIM(country) <> ''")
            ->whereNotNull('state')
            ->whereRaw("TRIM(state) <> ''")
            ->select('country', 'state')
            ->distinct()
            ->orderBy('country')
            ->orderBy('state')
            ->get()
            ->groupBy('country')
            ->map(fn ($rows) => $rows->pluck('state')->values());

        $platforms = RFPsPlatform::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = [
            'search' => $search,
            'use_keywords' => $userKeyword,
            'keywords' => $userKeyword ? $selectedKeywords : [],
            'keyword_condition' => $keywordCondition,
            'condition' => $keywordCondition,
            'status' => $status,
            'institution_id' => $institutionId,
            'rfps_platform_id' => $platformId,
            'date_open' => $dateOpen,
            'date_close' => $dateClose,
        ];

        return view('rfps.index', [
            'rfps' => $rfps,
            'filters' => $filters,
            'institutions' => $institutions,
            'platforms' => $platforms,
            'countries' => $countries,
            'statesByCountry' => $statesByCountry,
        ]);
    }

    public function scrape(Request $request, RfpScraperManager $scraperManager): RedirectResponse
    {
        $country = trim((string) $request->input('country'));
        $validCountries = Institution::query()
            ->whereNotNull('country')
            ->whereRaw("TRIM(country) <> ''")
            ->distinct()
            ->pluck('country')
            ->all();

        $validStates = Institution::query()
            ->where('country', $country)
            ->whereNotNull('state')
            ->whereRaw("TRIM(state) <> ''")
            ->distinct()
            ->pluck('state')
            ->all();

        $validated = $request->validate([
            'country' => ['required', 'string', Rule::in($validCountries)],
            'state' => ['required', 'string', Rule::in($validStates)],
            'institution_id' => 'nullable',
            'institution_name' => 'nullable|string|max:255',
            'platform_name' => 'required|string|max:255|exists:rfps_platforms,name',
            'type' => 'required|string|in:open,closed',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $country = $validated['country'];
        $state = $validated['state'];
        $institutionId = $validated['institution_id'] ?? null;
        $institutionInput = $validated['institution_name'] ?? '';
        $platformInput = $validated['platform_name'] ?? '';
        $type = strtolower($validated['type'] ?? 'open');
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $scrapeStartedAt = microtime(true);
        $scrapeContext = [
            'type' => $type,
            'country' => $country,
            'state' => $state,
            'institution_id' => $institutionId,
            'institution_name' => $institutionInput ?: null,
            'platform_name' => $platformInput ?: null,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'user_id' => $request->user()?->id,
        ];
        Log::info('RFP UI scrape started.', $scrapeContext);

        $statusEnum = match ($type) {
            'past', 'closed' => RfpStatus::PAST,
            'all' => RfpStatus::ALL,
            default => RfpStatus::OPEN,
        };

        $institutionName = '';
        $institutionObj = null;

        if (! empty($institutionId) && $institutionId !== 'all') {
            $inst = Institution::query()
                ->whereKey($institutionId)
                ->whereRaw('LOWER(TRIM(state)) = ?', [strtolower($state)])
                ->whereRaw('LOWER(TRIM(country)) = ?', [strtolower($country)])
                ->first();

            if ($inst) {
                $institutionName = $inst->name;
                $institutionObj = $inst;
            } else {
                return redirect()->back()->withInput()->with('error', 'The selected institution does not belong to the selected state.');
            }
        }

        if (empty($institutionName) && ! empty($institutionInput) && $institutionInput !== 'all') {
            $institutionName = trim($institutionInput);
            $institutionObj = Institution::query()
                ->where('name', 'like', "%{$institutionName}%")
                ->whereRaw('LOWER(TRIM(state)) = ?', [strtolower($state)])
                ->whereRaw('LOWER(TRIM(country)) = ?', [strtolower($country)])
                ->first();
        }

        $platformName = trim((string) ($platformInput !== 'all' ? $platformInput : ''));

        try {
            // BidNet Direct exposes public, platform-wide listings rather than institution-specific portals.
            // Route it through the existing RFP scrape form and manager, using its public listing URL.
            if (strcasecmp($platformName, 'BidNet Direct') === 0 || strcasecmp($platformName, 'BidNet') === 0) {
                if (! in_array(strtolower($country), ['usa', 'us', 'united states', 'united states of america'], true)
                    || BidNetDirectConfig::getLocationCode($state) === null) {
                    return redirect()->back()->withInput()->with('error', 'BidNet Direct location filtering currently supports supported U.S. states only. Choose a U.S. country/state or another platform.');
                }

                $platformRecord = RFPsPlatform::query()
                    ->where('name', 'like', '%BidNet%')
                    ->orWhere('platform_type', 'like', '%BidNet%')
                    ->first();

                $listingTypes = $type === 'all'
                    ? ['open', 'closed']
                    : [in_array($type, ['past', 'closed'], true) ? 'closed' : 'open'];
                $totalBidNetSaved = 0;
                $failedListingTypes = [];

                foreach ($listingTypes as $listingType) {
                    $runStartedAt = microtime(true);
                    $result = $scraperManager->scrapeUniversity(
                        universityName: 'BidNet Direct',
                        platform: 'BidNet Direct',
                        status: $listingType === 'closed' ? RfpStatus::PAST : RfpStatus::OPEN,
                        fromDate: $fromDate,
                        toDate: $toDate,
                        persist: true,
                        institution: null,
                        platformRecord: $platformRecord,
                        customPortalUrl: null,
                        options: ['platform' => 'BidNet Direct', 'location' => $state, 'country' => $country]
                    );

                    if ($result->isFailure()) {
                        $failedListingTypes[] = $listingType;
                        Log::warning('RFP UI BidNet scrape run failed.', $scrapeContext + [
                            'platform' => 'BidNet Direct',
                            'listing_type' => $listingType,
                            'error' => $result->errorMessage,
                            'duration_seconds' => round(microtime(true) - $runStartedAt, 2),
                        ]);
                        continue;
                    }

                    $totalBidNetSaved += $result->count();
                    Log::info('RFP UI BidNet scrape run completed.', $scrapeContext + [
                        'platform' => 'BidNet Direct',
                        'listing_type' => $listingType,
                        'rfp_count' => $result->count(),
                        'duration_seconds' => round(microtime(true) - $runStartedAt, 2),
                    ]);
                }

                Log::info('RFP UI BidNet scrape completed.', $scrapeContext + [
                    'platform' => 'BidNet Direct',
                    'listing_types' => $listingTypes,
                    'failed_listing_types' => $failedListingTypes,
                    'rfp_count' => $totalBidNetSaved,
                    'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2),
                ]);

                if (! empty($failedListingTypes)) {
                    return redirect()->back()->with('error', 'BidNet Direct scrape failed for: ' . implode(', ', $failedListingTypes) . '.');
                }

                return redirect()
                    ->route('rfps.index')
                    ->with('success', "BidNet Direct scrape completed for {$state}: {$totalBidNetSaved} RFP(s) saved or updated.");
            }

            $totalSaved = 0;
            $successRuns = 0;
            $failedRuns = 0;

            // Case Specific institution specified
            if (! empty($institutionName)) {
                $platformToUse = ! empty($platformName) ? $platformName : 'Bonfire';
                $platformRecord = RFPsPlatform::where('name', 'like', "%{$platformToUse}%")
                    ->orWhere('platform_type', 'like', "%{$platformToUse}%")
                    ->first();

                Log::info('RFP UI scrape run started.', $scrapeContext + ['institution' => $institutionName, 'platform' => $platformToUse]);
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
                    Log::warning('RFP UI scrape run failed.', $scrapeContext + ['institution' => $institutionName, 'platform' => $platformToUse, 'error' => $result->errorMessage, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);
                    return redirect()->back()->with('error', "Scraping failed for '{$institutionName}': {$result->errorMessage}");
                }

                $totalSaved = $result->count();
                Log::info('RFP UI scrape run completed.', $scrapeContext + ['institution' => $institutionName, 'platform' => $platformToUse, 'rfp_count' => $totalSaved, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);
                Log::info('RFP UI scrape completed.', $scrapeContext + ['success_runs' => 1, 'failed_runs' => 0, 'rfp_count' => $totalSaved, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);

                return redirect()
                    ->route('rfps.index')
                    ->with('success', "Scraped {$totalSaved} RFP(s) successfully for '{$institutionName}' on platform '{$platformToUse}'!");
            }

            // Case Platform specified without specific institution
            elseif (! empty($platformName)) {
                $platforms = RFPsPlatform::query()
                    ->where(function ($query) use ($platformName) {
                        $query->where('platform_type', 'like', "%{$platformName}%")
                            ->orWhere('name', 'like', "%{$platformName}%");
                    })
                    ->with(['rfpInstitutions' => function ($query) use ($state, $country) {
                        $query->whereRaw('LOWER(TRIM(institutions.state)) = ?', [strtolower($state)])
                            ->whereRaw('LOWER(TRIM(institutions.country)) = ?', [strtolower($country)]);
                    }])
                    ->get();

                if ($platforms->isEmpty()) {
                    return redirect()->back()->with('error', "No platform records found matching '{$platformName}'.");
                }

                foreach ($platforms as $pRecord) {
                    $instList = $pRecord->rfpInstitutions;
                    if ($instList->isEmpty()) {
                        $instList = Institution::query()
                            ->whereRaw('LOWER(TRIM(state)) = ?', [strtolower($state)])
                            ->whereRaw('LOWER(TRIM(country)) = ?', [strtolower($country)])
                            ->orderBy('name')
                            ->get();
                    }

                    foreach ($instList as $inst) {
                        Log::info('RFP UI scrape run started.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? $platformName]);
                        $runStartedAt = microtime(true);
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
                            Log::info('RFP UI scrape run completed.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? $platformName, 'rfp_count' => $result->count(), 'duration_seconds' => round(microtime(true) - $runStartedAt, 2)]);
                        } else {
                            $failedRuns++;
                            Log::warning('RFP UI scrape run failed.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? $platformName, 'error' => $result->errorMessage, 'duration_seconds' => round(microtime(true) - $runStartedAt, 2)]);
                        }
                    }
                }

                Log::info('RFP UI scrape completed.', $scrapeContext + ['success_runs' => $successRuns, 'failed_runs' => $failedRuns, 'rfp_count' => $totalSaved, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);
                return redirect()
                    ->route('rfps.index')
                    ->with('success', "Platform scrape completed for '{$platformName}': {$totalSaved} RFP(s) saved across {$successRuns} institution(s).");
            }

            // Case Bulk / All scrape
            else {
                $institutions = Institution::query()
                    ->whereRaw('LOWER(TRIM(state)) = ?', [strtolower($state)])
                    ->whereRaw('LOWER(TRIM(country)) = ?', [strtolower($country)])
                    ->with('rfpPlatforms')
                    ->orderBy('name')
                    ->get();

                $bidNetRecord = RFPsPlatform::query()
                    ->where(function ($query) {
                        $query->where('name', 'like', '%BidNet%')
                            ->orWhere('platform_type', 'like', '%BidNet%');
                    })
                    ->first();

                $listingTypes = $type === 'all'
                    ? ['open', 'closed']
                    : [in_array($type, ['past', 'closed'], true) ? 'closed' : 'open'];

                foreach ($listingTypes as $listingType) {
                    $bidNetStartedAt = microtime(true);
                    $bidNetResult = $scraperManager->scrapeUniversity(
                        universityName: 'BidNet Direct',
                        platform: 'BidNet Direct',
                        status: $listingType === 'closed' ? RfpStatus::PAST : RfpStatus::OPEN,
                        fromDate: $fromDate,
                        toDate: $toDate,
                        persist: true,
                        institution: null,
                        platformRecord: $bidNetRecord,
                        customPortalUrl: null,
                        options: ['platform' => 'BidNet Direct', 'location' => $state]
                    );

                    if ($bidNetResult->isSuccess()) {
                        $successRuns++;
                        $totalSaved += $bidNetResult->count();
                        Log::info('RFP UI state-wide BidNet scrape completed.', $scrapeContext + [
                            'platform' => 'BidNet Direct',
                            'listing_type' => $listingType,
                            'rfp_count' => $bidNetResult->count(),
                            'duration_seconds' => round(microtime(true) - $bidNetStartedAt, 2),
                        ]);
                    } else {
                        $failedRuns++;
                        Log::warning('RFP UI state-wide BidNet scrape failed.', $scrapeContext + [
                            'platform' => 'BidNet Direct',
                            'listing_type' => $listingType,
                            'error' => $bidNetResult->errorMessage,
                            'duration_seconds' => round(microtime(true) - $bidNetStartedAt, 2),
                        ]);
                    }
                }

                if ($institutions->isEmpty()) {
                    if ($successRuns > 0) {
                        return redirect()
                            ->route('rfps.index')
                            ->with('success', "State-wide scrape completed for {$state}: {$totalSaved} RFP(s) saved. No matching institutions were found for institution-specific platforms.");
                    }

                    return redirect()->back()->with('error', 'No institutions found in the selected state and BidNet Direct did not complete successfully.');
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
                        if (stripos((string) ($pRecord->name ?? ''), 'bidnet') !== false || stripos((string) ($pRecord->platform_type ?? ''), 'bidnet') !== false) {
                            continue;
                        }

                        Log::info('RFP UI scrape run started.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? 'Bonfire']);
                        $runStartedAt = microtime(true);
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
                            Log::info('RFP UI scrape run completed.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? 'Bonfire', 'rfp_count' => $result->count(), 'duration_seconds' => round(microtime(true) - $runStartedAt, 2)]);
                        } else {
                            $failedRuns++;
                            Log::warning('RFP UI scrape run failed.', $scrapeContext + ['institution' => $inst->name, 'platform' => $pRecord->name ?? 'Bonfire', 'error' => $result->errorMessage, 'duration_seconds' => round(microtime(true) - $runStartedAt, 2)]);
                        }
                    }
                }

                Log::info('RFP UI scrape completed.', $scrapeContext + ['success_runs' => $successRuns, 'failed_runs' => $failedRuns, 'rfp_count' => $totalSaved, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);
                return redirect()
                    ->route('rfps.index')
                    ->with('success', "All-institution scrape completed: {$totalSaved} RFP(s) saved across {$successRuns} platform run(s).");
            }
        } catch (\Throwable $e) {
            Log::error('RFP UI scrape crashed.', $scrapeContext + ['exception' => $e, 'duration_seconds' => round(microtime(true) - $scrapeStartedAt, 2)]);
            return redirect()->back()->with('error', "Scraping error: {$e->getMessage()}");
        }
    }
}
