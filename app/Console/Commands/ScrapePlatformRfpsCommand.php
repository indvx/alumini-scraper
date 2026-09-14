<?php

namespace App\Console\Commands;

use App\Enums\Rfp\RfpStatus;
use App\Models\RFPsPlatform;
use App\Services\Rfp\RfpScraperManager;
use Illuminate\Console\Command;

class ScrapePlatformRfpsCommand extends Command
{
    protected $signature = 'rfp:scrape-platform
                            {platform=Bonfire : Name or type of platform to scrape}
                            {--type=open : Opportunity type (open or past)}';

    protected $description = 'Scrape RFPs across all institutions for a given platform';

    public function handle(RfpScraperManager $manager): int
    {
        $platformInput = $this->argument('platform');
        $type = $this->option('type') ?? 'open';

        $this->info("Scanning platforms matching: {$platformInput}");

        $platforms = RFPsPlatform::where('platform_type', 'like', "%{$platformInput}%")
            ->orWhere('name', 'like', "%{$platformInput}%")
            ->with('institutions')
            ->get();

        if ($platforms->isEmpty()) {
            $this->error("No platforms found in database matching '{$platformInput}'.");

            return Command::FAILURE;
        }

        $totalScraped = 0;

        foreach ($platforms as $platformRecord) {
            if ($platformRecord->institutions->isEmpty()) {
                $this->warn("No institutions associated with platform '{$platformRecord->name}'.");

                continue;
            }

            foreach ($platformRecord->institutions as $institution) {
                $this->info("Scraping {$institution->name} on {$platformRecord->name}...");
                $result = $manager->scrapeUniversity(
                    universityName: $institution->name,
                    platform: $platformRecord->platform_type ?? 'Bonfire',
                    status: strtolower($type) === 'past' ? RfpStatus::PAST : RfpStatus::OPEN,
                    institution: $institution,
                    platformRecord: $platformRecord
                );

                if ($result->isFailure()) {
                    $this->error("Failed to scrape {$institution->name}: {$result->errorMessage}");
                } else {
                    $count = $result->count();
                    $totalScraped += $count;
                    $this->info("Successfully scraped {$count} RFP(s) for {$institution->name}.");
                }
            }
        }

        $this->info("Completed platform scrape. Total RFPs saved: {$totalScraped}");

        return Command::SUCCESS;
    }
}
