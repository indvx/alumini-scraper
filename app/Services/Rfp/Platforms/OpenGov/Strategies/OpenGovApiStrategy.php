<?php

namespace App\Services\Rfp\Platforms\OpenGov\Strategies;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpExtractionStrategy;
use App\Services\Rfp\Platforms\OpenGov\OpenGovConfig;
use App\Services\Rfp\Platforms\OpenGov\OpenGovNormalizer;
use Illuminate\Support\Facades\Http;

class OpenGovApiStrategy implements RfpExtractionStrategy
{
    public function __construct(
        protected OpenGovNormalizer $normalizer
    ) {}

    public function supports(RfpScrapeData $scrapeData): bool
    {
        $url = strtolower((string) $scrapeData->portalUrl);

        return str_contains($url, 'opengov.com') ||
            str_contains(strtolower($scrapeData->universityName), 'opengov') ||
            ! empty(OpenGovConfig::extractGovCode($scrapeData->portalUrl));
    }

    public function extract(RfpScrapeData $scrapeData): ScraperResult
    {
        $govCode = OpenGovConfig::extractGovCode($scrapeData->portalUrl)
            ?? $scrapeData->options['gov_code']
            ?? null;

        if (empty($govCode)) {
            return ScraperResult::failure(
                'Could not extract OpenGov government code from portal URL or options.',
                ScrapeMethod::API
            );
        }

        $apiUrl = OpenGovConfig::getPublicProjectsEndpoint($govCode);
        $status = 'all';
        if ($scrapeData->type === RfpStatus::PAST) {
            $status = 'closed';
        } else if ($scrapeData->type === RfpStatus::OPEN) {
            $status = 'open';
        }

        $payload = [
            'filters' => [
                [
                    'type' => 'status',
                    'value' => $status,
                ],
            ],
            'quickSearchQuery' => $scrapeData->options['query'] ?? null,
            'limit' => $scrapeData->options['limit'] ?? 150,
            'page' => $scrapeData->options['page'] ?? 1,
            'sortField' => 'proposalDeadline',
            'sortDirection' => 'DESC',
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36',
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
                'Origin' => 'https://procurement.opengov.com',
                'Referer' => 'https://procurement.opengov.com/',
            ])->timeout(15)->post($apiUrl, $payload);

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
