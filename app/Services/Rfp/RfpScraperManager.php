<?php

namespace App\Services\Rfp;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Models\Institution;
use App\Models\RFPsPlatform;
use App\Services\Rfp\Discovery\ProcurementPlatformDiscoveryService;
use App\Services\Rfp\Support\ScraperRegistry;
use Exception;

class RfpScraperManager
{
    public function __construct(
        protected ScraperRegistry $scraperRegistry,
        protected ProcurementPlatformDiscoveryService $discoveryService,
        protected RfpPersistenceService $persistenceService
    ) {}

    public function scrapeUniversity(
        string $universityName,
        string $platform = 'Bonfire',
        RfpStatus $status = RfpStatus::OPEN,
        ?string $fromDate = null,
        ?string $toDate = null,
        bool $persist = true,
        ?Institution $institution = null,
        ?RFPsPlatform $platformRecord = null
    ): ScraperResult {

        try {
            $discoveryResult = $this->discoveryService->discoverPortal(
                universityName: $universityName,
                platformName: $platform,
                institution: $institution,
                platformRecord: $platformRecord
            );
        } catch (Exception $e) {
            return ScraperResult::failure($e->getMessage(), ScrapeMethod::API);
        }

        if (! $discoveryResult->isSuccess()) {
            return ScraperResult::failure(
                "Procurement portal URL for '{$universityName}' on platform '{$platform}' was not found in database.",
                ScrapeMethod::API
            );
        }

        $resolvedInstitution = $discoveryResult->institution ?? $institution;
        $resolvedPlatform = $discoveryResult->platformRecord ?? $platformRecord;

        $scrapeData = new RfpScrapeData(
            universityName: $universityName,
            portalUrl: $discoveryResult->portalUrl,
            type: $status,
            fromDate: $fromDate,
            toDate: $toDate,
            institutionId: $resolvedInstitution?->id,
            platformId: $resolvedPlatform?->id
        );

        $scraper = $this->scraperRegistry->getScraperForPlatform($platform);
        $result = $scraper->scrape($scrapeData);
        dd($result);
        if ($persist && ! empty($result->rfps)) {
            $this->persistenceService->saveMany($result->rfps);
        }

        return $result;
    }
}
