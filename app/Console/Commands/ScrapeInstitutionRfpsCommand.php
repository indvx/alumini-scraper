<?php

namespace App\Console\Commands;

use App\Enums\Rfp\RfpStatus;
use App\Services\Rfp\RfpScraperManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapeInstitutionRfpsCommand extends Command
{
    protected $signature = 'rfp:scrape-institution
                            {university : Name of the institution}
                            {--platform=Bonfire : Target platform (Bonfire, Jaggaer, PlanetBids, OpenGov)}
                            {--type=open : Opportunity type (open or past)}
                            {--from= : Start date filter (YYYY-MM-DD)}
                            {--to= : End date filter (YYYY-MM-DD)}';

    protected $description = 'Scrape RFPs for a specific university/institution';

    public function handle(RfpScraperManager $manager): int
    {
        $university = $this->argument('university');
        $platform = $this->option('platform') ?? 'Bonfire';
        $type = $this->option('type') ?? 'open';
        $fromDate = $this->option('from');
        $toDate = $this->option('to');

        $statusEnum = strtolower($type) === 'past' || strtolower($type) === 'closed'
            ? RfpStatus::PAST
            : RfpStatus::OPEN;

        $this->info("Scraping {$platform} {$type} RFPs for {$university}...");

        $result = $manager->scrapeUniversity(
            universityName: $university,
            platform: $platform,
            status: $statusEnum,
            fromDate: $fromDate,
            toDate: $toDate,
            persist: true
        );

        if ($result->isFailure()) {
            $this->error("Scraping failed: {$result->errorMessage}");

            return Command::FAILURE;
        }

        $this->info("Execution status: {$result->status->value}");
        $this->info("Found {$result->count()} RFP(s).");

        if (! empty($result->rfps)) {
            $table = array_map(fn($rfp) => [
                'Project ID' => $rfp->projectId,
                'Ref ID' => $rfp->referenceId ?? 'N/A',
                'Title' => Str::limit($rfp->title, 40),
                'Close Date' => $rfp->dateClose ?? 'N/A',
                'Source' => $rfp->source instanceof \BackedEnum ? $rfp->source->value : (string) ($rfp->source ?? $result->method?->value),
            ], array_slice($result->rfps, 0, 15));

            $this->table(['Project ID', 'Ref ID', 'Title', 'Close Date', 'Source'], $table);
        }

        return Command::SUCCESS;
    }
}
