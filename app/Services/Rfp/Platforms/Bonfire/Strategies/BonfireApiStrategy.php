<?php

namespace App\Services\Rfp\Platforms\Bonfire\Strategies;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpExtractionStrategy;
use App\Services\Rfp\Platforms\Bonfire\BonfireConfig;
use App\Services\Rfp\Platforms\Bonfire\BonfireNormalizer;
use Illuminate\Support\Facades\Http;

class BonfireApiStrategy implements RfpExtractionStrategy
{
    public function __construct(
        protected BonfireNormalizer $normalizer
    ) {}

    public function supports(RfpScrapeData $scrapeData): bool
    {
        return ! empty($scrapeData->portalUrl) && str_contains(strtolower($scrapeData->portalUrl), 'bonfirehub.com');
    }

    public function extract(RfpScrapeData $scrapeData): ScraperResult
    {
        $baseUrl = rtrim((string) $scrapeData->portalUrl, '/');
        $endpoint = BonfireConfig::getApiEndpoint($scrapeData->type->value);
        $apiUrl = $baseUrl . $endpoint;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'Accept' => 'application/json',
            ])->timeout(15)->get($apiUrl);

            if (! $response->successful()) {
                return ScraperResult::failure("HTTP request failed with status {$response->status()}", ScrapeMethod::API);
            }

            $rawPayload = $response->json();
            $rfps = $this->normalizer->normalize($rawPayload ?? [], $scrapeData, ScrapeMethod::API);

            return ScraperResult::success($rfps, ScrapeMethod::API);
        } catch (\Throwable $e) {
            return ScraperResult::failure($e->getMessage(), ScrapeMethod::API);
        }
    }
}
