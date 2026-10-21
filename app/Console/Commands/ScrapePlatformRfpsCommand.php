<?php

namespace App\Console\Commands;

use App\Enums\Rfp\RfpStatus;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapePlatformRfpsCommand extends Command
{
    protected $signature = 'rfp:scrape-platform
                            {platform=Bonfire : Name or type of platform to scrape}
                            {--type=open : Opportunity type (open or past)}';

    protected $description = 'Scrape RFPs across all institutions for a given platform';

    public function handle(RfpScraperManager $manager): int
    {
        $startedAt = microtime(true);
        $platformInput = $this->argument('platform');
        $type = $this->option('type') ?? 'open';

        Log::info('CLI platform RFP scrape started.', ['platform_filter' => $platformInput, 'type' => $type]);
        $this->info("Scanning platforms matching: {$platformInput}");

        $platforms = RFPsPlatform::where('platform_type', 'like', "%{$platformInput}%")
            ->orWhere('name', 'like', "%{$platformInput}%")
            ->with('rfpInstitutions')
            ->get();

        if ($platforms->isEmpty()) {
            Log::warning('CLI platform RFP scrape found no matching platforms.', ['platform_filter' => $platformInput]);
            $this->error("No platforms found in database matching '{$platformInput}'.");

            return Command::FAILURE;
        }

        $totalScraped = 0;

        foreach ($platforms as $platformRecord) {
            if ($platformRecord->rfpInstitutions->isEmpty()) {
                $this->warn("No institutions associated with platform '{$platformRecord->name}'.");

                continue;
            }

            foreach ($platformRecord->rfpInstitutions as $institution) {
                Log::info('CLI platform scrape run started.', ['institution' => $institution->name, 'platform' => $platformRecord->name]);
                $this->info("Scraping {$institution->name} on {$platformRecord->name}...");
                $result = $manager->scrapeUniversity(
                    universityName: $institution->name,
                    platform: $platformRecord->name ?? $platformRecord->platform_type ?? 'Bonfire',
                    status: strtolower($type) === 'past' ? RfpStatus::PAST : RfpStatus::OPEN,
                    institution: $institution,
                    platformRecord: $platformRecord
                );

                if ($result->isFailure()) {
                    Log::warning('CLI platform scrape run failed.', ['institution' => $institution->name, 'platform' => $platformRecord->name, 'error' => $result->errorMessage]);
                    $this->error("Failed to scrape {$institution->name}: {$result->errorMessage}");
                } else {
                    $count = $result->count();
                    $totalScraped += $count;
                    Log::info('CLI platform scrape run completed.', ['institution' => $institution->name, 'platform' => $platformRecord->name, 'rfp_count' => $count]);
                    $this->info("Successfully scraped {$count} RFP(s) for {$institution->name}.");
                }
            }
        }

        Log::info('CLI platform RFP scrape completed.', ['platform_filter' => $platformInput, 'total_rfps' => $totalScraped, 'duration_seconds' => round(microtime(true) - $startedAt, 2)]);
        $this->info("Completed platform scrape. Total RFPs saved: {$totalScraped}");

        return Command::SUCCESS;
    }
}
