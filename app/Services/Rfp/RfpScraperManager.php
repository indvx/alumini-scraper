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
use Illuminate\Support\Facades\Log;

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
        ?RFPsPlatform $platformRecord = null,
        ?string $customPortalUrl = null,
        array $options = []
    ): ScraperResult {
        try {
            $startedAt = microtime(true);
            $context = ['institution' => $universityName, 'platform' => $platform, 'status' => $status->value, 'from_date' => $fromDate, 'to_date' => $toDate];
            Log::info('RFP scraper manager started.', $context + ['custom_portal_url' => $customPortalUrl]);
            $portalUrl = $customPortalUrl;
            $resolvedInstitution = $institution;
            $resolvedPlatform = $platformRecord;

            $isStateScopedBidNetSearch = stripos($platform, 'bidnet') !== false
                && ! empty($options['location']);

            if (empty($portalUrl) && ! $isStateScopedBidNetSearch) {
                try {
                    $discoveryResult = $this->discoveryService->discoverPortal(
                        universityName: $universityName,
                        platformName: $platform,
                        institution: $institution,
                        platformRecord: $platformRecord,
                        allowExternalLookup: false
                    );
                } catch (Exception $e) {
                    Log::warning('RFP portal discovery failed.', $context + ['exception' => $e]);
                    return ScraperResult::failure($e->getMessage(), ScrapeMethod::API);
                }

                if (! $discoveryResult->isSuccess()) {
                    Log::warning('RFP portal discovery returned no portal.', $context);
                    return ScraperResult::failure(
                        "Procurement portal URL for '{$universityName}' on platform '{$platform}' was not found in database.",
                        ScrapeMethod::API
                    );
                }

                $portalUrl = $discoveryResult->portalUrl;
                $resolvedInstitution = $discoveryResult->institution ?? $institution;
                $resolvedPlatform = $discoveryResult->platformRecord ?? $platformRecord;
            }

            if (! $resolvedPlatform) {
                $resolvedPlatform = RFPsPlatform::where('name', 'like', "%{$platform}%")
                    ->orWhere('platform_type', 'like', "%{$platform}%")
                    ->first();
            }

            $scrapeData = new RfpScrapeData(
                universityName: $universityName,
                portalUrl: $portalUrl,
                type: $status,
                fromDate: $fromDate,
                toDate: $toDate,
                institutionId: $resolvedInstitution?->id,
                platformId: $resolvedPlatform?->id,
                options: $options
            );

            $scraper = $this->scraperRegistry->getScraperForPlatform($platform);
            Log::info('RFP platform scraper selected.', $context + ['scraper_class' => get_class($scraper), 'portal_url' => $portalUrl]);
            $result = $scraper->scrape($scrapeData);
            Log::info('RFP platform scraper returned.', $context + ['success' => $result->isSuccess(), 'rfp_count' => $result->count(), 'method' => $result->method?->value, 'error' => $result->errorMessage]);
            if ($persist && ! empty($result->rfps)) {
                $saved = $this->persistenceService->saveMany($result->rfps);
                Log::info('RFP scrape persistence completed.', $context + ['received_count' => count($result->rfps), 'saved_count' => count($saved)]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error("Scraping error for '{$universityName}' on platform '{$platform}': {$e->getMessage()}", [
                'institution' => $universityName, 'platform' => $platform, 'exception' => $e,
            ]);

            return ScraperResult::failure("Scraping exception: {$e->getMessage()}", ScrapeMethod::API);
        }
    }
}
