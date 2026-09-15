<?php

namespace App\Services\Rfp\Platforms\Bonfire\Strategies;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpExtractionStrategy;
use App\Services\Rfp\Platforms\Bonfire\BonfireNormalizer;
use Illuminate\Support\Facades\Http;

class BonfireUiStrategy implements RfpExtractionStrategy
{
    public function __construct(protected BonfireNormalizer $normalizer) {}

    public function supports(RfpScrapeData $scrapeData): bool
    {
        return ! empty($scrapeData->portalUrl);
    }

    public function extract(RfpScrapeData $scrapeData): ScraperResult
    {
        try {
            $portalUrl = rtrim((string) $scrapeData->portalUrl, '/');
            $endpoint = $scrapeData->type === RfpStatus::PAST ? $portalUrl.'/PublicPortal/getPastPublicOpportunitiesSectionData' : $portalUrl.'/PublicPortal/getOpenPublicOpportunitiesSectionData';
            $response = Http::timeout(30)->withHeaders(
                [
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $portalUrl.'/portal/',
                    'User-Agent' => 'Mozilla/5.0',
                ]
            )->get($endpoint);

            if (! $response->successful()) {
                return ScraperResult::failure('Bonfire UI request failed with HTTP status '.$response->status(), ScrapeMethod::UI, ['url' => $endpoint, 'status' => $response->status()]);
            }

            $data = $response->json();
            if (! is_array($data)) {
                return ScraperResult::failure('Bonfire UI returned an invalid JSON response.', ScrapeMethod::UI, ['url' => $endpoint, 'body' => $response->body()]);
            }

            $rfps = $this->normalizer->normalize($data, $scrapeData, 'ui');
            if (empty($rfps)) {
                return ScraperResult::failure('Bonfire UI returned no matching RFPs.', ScrapeMethod::UI, ['url' => $endpoint, 'project_count' => count($data['projects'] ?? [])]);
            }

            return ScraperResult::success($rfps, ScrapeMethod::UI, ['url' => $endpoint, 'project_count' => count($data['projects'] ?? [])]);
        } catch (\Throwable $e) {
            return ScraperResult::failure($e->getMessage(), ScrapeMethod::UI);
        }
    }
}
