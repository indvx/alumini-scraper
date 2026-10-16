<?php

namespace App\Services\Rfp\Platforms\BidNetDirect\Strategies;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpExtractionStrategy;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectConfig;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectNormalizer;
use Illuminate\Support\Facades\Http;
use Throwable;

class BidNetDirectUrlStrategy implements RfpExtractionStrategy
{
    public function __construct(
        protected BidNetDirectNormalizer $normalizer
    ) {}

    /**
     * Determine if this strategy supports the given scrape request.
     */
    public function supports(RfpScrapeData $scrapeData): bool
    {
        $url = strtolower((string) $scrapeData->portalUrl);
        $name = strtolower($scrapeData->universityName);
        $platformOption = strtolower((string) ($scrapeData->options['platform'] ?? ''));

        return str_contains($url, 'bidnetdirect.com')
            || str_contains($url, 'bidnet')
            || str_contains($name, 'bidnet')
            || $platformOption === 'bidnet direct'
            || $platformOption === 'bidnetdirect'
            || ! empty($scrapeData->portalUrl);
    }

    /**
     * Extract RFPs from BidNet Direct portal URL or provided payload.
     */
    public function extract(RfpScrapeData $scrapeData): ScraperResult
    {
        $startedAt = microtime(true);
        Log::info('BidNet Direct extraction started.', ['institution' => $scrapeData->universityName, 'portal_url' => $scrapeData->portalUrl, 'status' => $scrapeData->type->value]);
        try {
            if (! empty($scrapeData->options['html']) && is_string($scrapeData->options['html'])) {
                $rfps = $this->normalizer->normalize(
                    ['html' => $scrapeData->options['html']],
                    $scrapeData,
                    ScrapeMethod::UI
                );

                return ScraperResult::success($rfps, ScrapeMethod::UI, [
                    'source' => 'html_options',
                    'count' => count($rfps),
                ]);
            }

            if (! empty($scrapeData->options['solicitations']) && is_array($scrapeData->options['solicitations'])) {
                $rfps = $this->normalizer->normalize(
                    ['solicitations' => $scrapeData->options['solicitations']],
                    $scrapeData,
                    ScrapeMethod::UI
                );

                return ScraperResult::success($rfps, ScrapeMethod::UI, [
                    'source' => 'solicitations_options',
                    'count' => count($rfps),
                ]);
            }

            $targetUrl = $this->resolveTargetUrl($scrapeData);

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => BidNetDirectConfig::ROOT_URL.'/',
            ])->timeout(20)->get($targetUrl);

            Log::debug('BidNet Direct HTTP response received.', ['url' => $targetUrl, 'http_status' => $response->status()]);
            if (! $response->successful()) {
                Log::warning('BidNet Direct request returned unsuccessful HTTP status.', ['url' => $targetUrl, 'http_status' => $response->status()]);
                return ScraperResult::failure(
                    "HTTP request to BidNet Direct failed with status {$response->status()}",
                    ScrapeMethod::UI,
                    ['url' => $targetUrl, 'status' => $response->status()]
                );
            }

            $html = $response->body();

            if (str_contains($html, 'challenge.js') && ! str_contains($html, 'solicitationsList')) {
                Log::warning('BidNet Direct access blocked by WAF challenge.', ['url' => $targetUrl]);
                return ScraperResult::failure(
                    'BidNet Direct access was blocked by an automated WAF/bot verification challenge.',
                    ScrapeMethod::UI,
                    ['url' => $targetUrl]
                );
            }

            $rfps = $this->normalizer->normalize(
                ['html' => $html],
                $scrapeData,
                ScrapeMethod::UI
            );

            Log::info('BidNet Direct page normalized.', ['url' => $targetUrl, 'rfp_count' => count($rfps), 'duration_seconds' => round(microtime(true) - $startedAt, 2)]);
            return ScraperResult::success($rfps, ScrapeMethod::UI, [
                'url' => $targetUrl,
                'count' => count($rfps),
            ]);
        } catch (Throwable $e) {
            Log::error('BidNet Direct extraction exception.', ['institution' => $scrapeData->universityName, 'exception' => $e, 'duration_seconds' => round(microtime(true) - $startedAt, 2)]);
            return ScraperResult::failure(
                "BidNet Direct extraction exception: {$e->getMessage()}",
                ScrapeMethod::UI
            );
        }
    }

    /**
     * Resolve the target scrape URL based on portalUrl or search parameters.
     */
    protected function resolveTargetUrl(RfpScrapeData $scrapeData): string
    {
        $portalUrl = trim((string) $scrapeData->portalUrl);

        if ($portalUrl !== '' && (str_starts_with($portalUrl, 'http://') || str_starts_with($portalUrl, 'https://'))) {
            return $portalUrl;
        }

        $type = $scrapeData->type === RfpStatus::PAST || $scrapeData->type === RfpStatus::CLOSED
            ? 'closed'
            : 'open';

        $keywords = $scrapeData->options['keywords'] ?? $scrapeData->universityName;
        if (strtolower((string) $keywords) === 'bidnet direct' || strtolower((string) $keywords) === 'bidnet') {
            $keywords = null;
        }

        return BidNetDirectConfig::buildSearchUrl([
            'type' => $type,
            'keywords' => $keywords,
            'location' => $scrapeData->options['location'] ?? null,
            'searchContentGroupId' => $scrapeData->options['searchContentGroupId'] ?? null,
            'category' => $scrapeData->options['category'] ?? null,
            'publishDate' => $scrapeData->options['publishDate'] ?? null,
            'page' => $scrapeData->options['page'] ?? 1,
        ]);
    }
}
