<?php

namespace App\Services\Rfp\Platforms\Bonfire\Strategies;


use Illuminate\Support\Facades\Log;
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
        $apiUrl = $baseUrl.$endpoint;
        Log::debug('Requesting Bonfire API endpoint.', ['institution' => $scrapeData->universityName, 'endpoint' => $apiUrl, 'status' => $scrapeData->type->value]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'Accept' => 'application/json',
            ])->timeout(15)->get($apiUrl);

            Log::debug('Bonfire API response received.', ['institution' => $scrapeData->universityName, 'http_status' => $response->status()]);
            if (! $response->successful()) {
                Log::warning('Bonfire API request returned unsuccessful HTTP status.', ['institution' => $scrapeData->universityName, 'http_status' => $response->status()]);
                return ScraperResult::failure("HTTP request failed with status {$response->status()}", ScrapeMethod::API);
            }

            $rawPayload = $response->json();
            $rfps = $this->normalizer->normalize($rawPayload ?? [], $scrapeData, ScrapeMethod::API);
            Log::info('Bonfire API payload normalized.', ['institution' => $scrapeData->universityName, 'rfp_count' => count($rfps)]);

            return ScraperResult::success($rfps, ScrapeMethod::API);
        } catch (\Throwable $e) {
            return ScraperResult::failure($e->getMessage(), ScrapeMethod::API);
        }
    }
}
