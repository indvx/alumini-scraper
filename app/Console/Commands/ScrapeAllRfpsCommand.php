<?php

namespace App\Console\Commands;

use App\Enums\Rfp\RfpStatus;
use App\Models\Institution;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapeAllRfpsCommand extends Command
{
    protected $signature = 'rfp:scrape-all
                            {--type=open : Opportunity type (open or past or all)}
                            {--institution= : Optional institution filter to match specific university/institution name}
                            {--platform= : Optional platform filter to match specific platform(s)}
                            {--limit=all : Maximum number of institutions to scrape}
                            {--from= : Start date filter (YYYY-MM-DD)}
                            {--to= : End date filter (YYYY-MM-DD)}
                            {--delay=0 : Delay between scraping runs in seconds}';

    protected $description = 'Scrape RFPs for all institutions across all registered platforms based on institution country';

    public function handle(RfpScraperManager $manager): int
    {
        $startedAt = microtime(true);
        $type = $this->option('type') ?? 'open';
        $institutionFilter = $this->option('institution');
        $platformFilter = $this->option('platform');
        $limit = $this->option('limit') === 'all' ? null : (int) $this->option('limit');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $delay = (int) ($this->option('delay') ?? 0);

        Log::info('CLI RFP scrape-all started.', ['type' => $type, 'institution_filter' => $institutionFilter, 'platform_filter' => $platformFilter, 'limit' => $limit, 'from_date' => $fromDate, 'to_date' => $toDate, 'delay' => $delay]);
        $statusEnum = match (strtolower((string) $type)) {
            'past' => RfpStatus::PAST,
            'all' => RfpStatus::ALL,
            default => RfpStatus::OPEN,
        };

        // BidNet Direct is a platform-wide public listing, not an institution-specific portal.
        // When explicitly selected, scrape it once through the same manager/persistence pipeline.
        if (! empty($platformFilter) && stripos($platformFilter, 'bidnet') !== false) {
            $platformRecord = RFPsPlatform::query()
                ->where('name', 'like', '%BidNet%')
                ->orWhere('platform_type', 'like', '%BidNet%')
                ->first();

            $listingTypes = strtolower((string) $type) === 'all' ? ['open', 'closed'] :
                [(strtolower((string) $type) === 'past') ? 'closed' : 'open'];
            $total = 0;
            $failed = false;

            foreach ($listingTypes as $listingType) {
                $result = $manager->scrapeUniversity(
                    universityName: 'BidNet Direct',
                    platform: 'BidNet Direct',
                    status: $listingType === 'closed' ? RfpStatus::PAST : RfpStatus::OPEN,
                    fromDate: $fromDate,
                    toDate: $toDate,
                    persist: true,
                    institution: null,
                    platformRecord: $platformRecord,
                    customPortalUrl: BidNetDirectConfig::getPublicPortalUrl($listingType),
                    options: ['platform' => 'BidNet Direct']
                );

                if ($result->isFailure()) {
                    $failed = true;
                    $this->error("BidNet Direct ({$listingType}) failed: {$result->errorMessage}");
                } else {
                    $count = $result->count();
                    $total += $count;
                    $this->info("BidNet Direct ({$listingType}): {$count} RFP(s) saved or updated.");
                }

                if ($delay > 0) {
                    sleep($delay);
                }
            }

            $this->info("BidNet Direct scrape completed. Total RFPs saved or updated: {$total}");

            Log::info('CLI BidNet Direct scrape-all completed.', ['total_rfps' => $total, 'failed' => $failed, 'duration_seconds' => round(microtime(true) - $startedAt, 2)]);
            return $failed ? Command::FAILURE : Command::SUCCESS;
        }

        // Query Institutions with eager loaded attached rfpPlatforms
        $institutionQuery = Institution::with(['rfpPlatforms' => function ($query) use ($platformFilter) {
            if (! empty($platformFilter)) {
                $query->where(function ($q) use ($platformFilter) {
                    $q->where('platform_type', 'like', "%{$platformFilter}%")
                        ->orWhere('name', 'like', "%{$platformFilter}%");
                });
            }
        }]);

        if (! empty($institutionFilter)) {
            $institutionQuery->where('name', 'like', "%{$institutionFilter}%");
        }

        if ($limit) {
            $institutionQuery->limit($limit);
        }

        $institutions = $institutionQuery->get();

        if ($institutions->isEmpty()) {
            Log::warning('CLI RFP scrape-all found no matching institutions.', ['institution_filter' => $institutionFilter, 'platform_filter' => $platformFilter]);
            $this->error('No matching institutions found in database.');

            return Command::FAILURE;
        }

        Log::info('CLI RFP scrape-all selected institutions.', ['institution_count' => $institutions->count(), 'status' => $statusEnum->value]);
        $this->info("Starting RFP scrape process across {$institutions->count()} institution(s) (limit: {$limit})...");
        $this->info("Status mode: {$statusEnum->value}");

        $totalRfpsSaved = 0;
        $successCount = 0;
        $failCount = 0;
        $summaryTable = [];

        // Include BidNet Direct in the default all-platform run exactly once.
        // Its public listing is platform-wide, so do not scrape it once per institution.
        if (empty($platformFilter)) {
            $bidNetRecord = RFPsPlatform::query()
                ->where('name', 'like', '%BidNet%')
                ->orWhere('platform_type', 'like', '%BidNet%')
                ->first();

            $listingTypes = strtolower((string) $type) === 'all' ? ['open', 'closed'] :
                [(strtolower((string) $type) === 'past') ? 'closed' : 'open'];

            foreach ($listingTypes as $listingType) {
                $this->output->write(" -> Scraping <comment>BidNet Direct</comment> ({$listingType})... ");
                $bidNetResult = $manager->scrapeUniversity(
                    universityName: 'BidNet Direct',
                    platform: 'BidNet Direct',
                    status: $listingType === 'closed' ? RfpStatus::PAST : RfpStatus::OPEN,
                    fromDate: $fromDate,
                    toDate: $toDate,
                    persist: true,
                    institution: null,
                    platformRecord: $bidNetRecord,
                    customPortalUrl: BidNetDirectConfig::getPublicPortalUrl($listingType),
                    options: ['platform' => 'BidNet Direct']
                );

                if ($bidNetResult->isFailure()) {
                    $failCount++;
                    $this->error("FAILED ({$bidNetResult->errorMessage})");
                    $summaryTable[] = [
                        'Institution' => 'Platform-wide',
                        'Platform' => "BidNet Direct ({$listingType})",
                        'Status' => 'Failed',
                        'RFPs Count' => 0,
                    ];
                } else {
                    $successCount++;
                    $count = $bidNetResult->count();
                    $totalRfpsSaved += $count;
                    $this->info("<info>SUCCESS</info> ({$count} RFP(s) found)");
                    $summaryTable[] = [
                        'Institution' => 'Platform-wide',
                        'Platform' => "BidNet Direct ({$listingType})",
                        'Status' => 'Success',
                        'RFPs Count' => $count,
                    ];
                }

                if ($delay > 0) {
                    sleep($delay);
                }
            }
        }
        foreach ($institutions as $institution) {
            $instCountry = $institution->country;

            $platformQuery = RFPsPlatform::query();

            if (! empty($platformFilter)) {
                $platformQuery->where(function ($q) use ($platformFilter) {
                    $q->where('platform_type', 'like', "%{$platformFilter}%")->orWhere('name', 'like', "%{$platformFilter}%");
                });
            }

            if (! empty($instCountry)) {
                $platformQuery->byCountry($instCountry);
            }

            $platforms = $platformQuery->get();

            if ($platforms->isEmpty()) {
                $this->warn("No platforms available to scrape for institution '{$institution->name}'. Skipping.");

                continue;
            }

            $locationTag = ! empty($instCountry) ? " [{$instCountry}]" : '';
            $this->info("\n--- Processing Institution: {$institution->name}{$locationTag} ({$platforms->count()} platform(s)) ---");

            foreach ($platforms as $platformRecord) {
                $platformName = $platformRecord->name;

                // The platform-wide BidNet pass is handled separately below.
                if (stripos((string) $platformName, 'bidnet') !== false) {
                    continue;
                }
                if (! $platformName) {
                    $this->warn("No platform name available for institution '{$institution->name}'. Skipping.");

                    continue;
                }
                $this->output->write(" -> Scraping <comment>{$institution->name}</comment> on <comment>{$platformName}</comment>... ");

                $result = $manager->scrapeUniversity(
                    universityName: $institution->name,
                    platform: $platformName,
                    status: $statusEnum,
                    fromDate: $fromDate,
                    toDate: $toDate,
                    persist: true,
                    institution: $institution,
                    platformRecord: $platformRecord
                );

                if ($result->isFailure()) {
                    $failCount++;
                    $this->error("FAILED ({$result->errorMessage})");
                    $summaryTable[] = [
                        'Institution' => Str::limit($institution->name, 25),
                        'Platform' => Str::limit($platformName, 20),
                        'Status' => 'Failed',
                        'RFPs Count' => 0,
                    ];
                } else {
                    $successCount++;
                    $count = $result->count();
                    $totalRfpsSaved += $count;
                    $this->info("<info>SUCCESS</info> ({$count} RFP(s) found)");
                    $summaryTable[] = [
                        'Institution' => Str::limit($institution->name, 25),
                        'Platform' => Str::limit($platformName, 20),
                        'Status' => 'Success',
                        'RFPs Count' => $count,
                    ];
                }

                if ($delay > 0) {
                    sleep($delay);
                }
            }
        }

        $this->info("\n==============================================");
        $this->info('RFP Scrape Finished!');
        $this->info("Total Successful Runs: {$successCount}");
        $this->info("Total Failed Runs: {$failCount}");
        $this->info("Total RFPs Saved: {$totalRfpsSaved}");
        $this->info('==============================================');

        if (! empty($summaryTable)) {
            $this->table(['Institution', 'Platform', 'Status', 'RFPs Count'], $summaryTable);
        }

        return Command::SUCCESS;
    }
}
