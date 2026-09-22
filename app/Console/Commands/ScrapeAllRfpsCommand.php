<?php

namespace App\Console\Commands;

use App\Enums\Rfp\RfpStatus;
use App\Models\Institution;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapeAllRfpsCommand extends Command
{
    protected $signature = 'rfp:scrape-all
                            {--type=open : Opportunity type (open or past or all)}
                            {--institution= : Optional institution filter to match specific university/institution name}
                            {--platform= : Optional platform filter to match specific platform(s)}
                            {--limit=20 : Maximum number of institutions to scrape}
                            {--from= : Start date filter (YYYY-MM-DD)}
                            {--to= : End date filter (YYYY-MM-DD)}
                            {--delay=0 : Delay between scraping runs in seconds}';

    protected $description = 'Scrape RFPs for all institutions across all registered platforms based on institution country';

    public function handle(RfpScraperManager $manager): int
    {
        $type = $this->option('type') ?? 'open';
        $institutionFilter = $this->option('institution');
        $platformFilter = $this->option('platform');
        $limit = (int) ($this->option('limit') ?? 20);
        $fromDate = $this->option('from');
        $toDate = $this->option('to');
        $delay = (int) ($this->option('delay') ?? 0);

        $statusEnum = match (strtolower((string) $type)) {
            'past' => RfpStatus::PAST,
            'all' => RfpStatus::ALL,
            default => RfpStatus::OPEN,
        };

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

        if ($limit > 0) {
            $institutionQuery->limit($limit);
        }

        $institutions = $institutionQuery->get();

        if ($institutions->isEmpty()) {
            $this->error('No matching institutions found in database.');

            return Command::FAILURE;
        }

        $this->info("Starting RFP scrape process across {$institutions->count()} institution(s) (limit: {$limit})...");
        $this->info("Status mode: {$statusEnum->value}");

        $totalRfpsSaved = 0;
        $successCount = 0;
        $failCount = 0;
        $summaryTable = [];
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
            // }

            if ($platforms->isEmpty()) {
                $this->warn("No platforms available to scrape for institution '{$institution->name}'. Skipping.");
                continue;
            }

            $locationTag = ! empty($instCountry) ? " [{$instCountry}]" : '';
            $this->info("\n--- Processing Institution: {$institution->name}{$locationTag} ({$platforms->count()} platform(s)) ---");

            foreach ($platforms as $platformRecord) {
                $platformName = $platformRecord->name;
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
