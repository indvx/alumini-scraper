<?php

namespace App\Services\Rfp\Platforms\BidNetDirect\Strategies;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpExtractionStrategy;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectConfig;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
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

            $defaultUa = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';
            $userAgent = Cache::get('bidnet_user_agent') ?: $defaultUa;

            $headers = [
                'User-Agent' => $userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => BidNetDirectConfig::ROOT_URL.'/solicitations/open-bids',
            ];

            $wafToken = $scrapeData->options['waf_token']
                ?? Cache::get('bidnet_waf_token')
                ?? config('services.bidnet.waf_token');

            if (! empty($wafToken)) {
                $trimmedToken = trim((string) $wafToken);
                $headers['Cookie'] = str_starts_with($trimmedToken, 'aws-waf-token=')
                    ? $trimmedToken
                    : "aws-waf-token={$trimmedToken}";
            }

            $response = Http::withHeaders($headers)->timeout(20)->get($targetUrl);

            Log::debug('BidNet Direct HTTP response received.', ['url' => $targetUrl, 'http_status' => $response->status()]);

            $body = $response->body();
            $wafAction = strtolower($response->header('x-amzn-waf-action', ''));
            $isWafChallenge = $wafAction === 'captcha'
                || $wafAction === 'challenge'
                || str_contains($body, 'challenge.js')
                || str_contains($body, 'captcha.js')
                || str_contains($body, 'Human Verification')
                || ($response->status() === 405 && str_contains($response->header('server', ''), 'awselb'));

            if (($isWafChallenge && ! str_contains($body, 'solicitationsList')) || ! empty($scrapeData->options['use_browser'])) {
                Log::info('BidNet Direct encountered AWS WAF CAPTCHA. Automatically launching browser for verification...', [
                    'url' => $targetUrl,
                    'http_status' => $response->status(),
                    'waf_action' => $wafAction,
                ]);

                return $this->extractWithBrowser($targetUrl, $scrapeData);
            }

            if (! $response->successful()) {
                Log::warning('BidNet Direct request returned unsuccessful HTTP status.', ['url' => $targetUrl, 'http_status' => $response->status()]);

                return ScraperResult::failure(
                    "HTTP request to BidNet Direct failed with status {$response->status()}",
                    ScrapeMethod::UI,
                    ['url' => $targetUrl, 'status' => $response->status()]
                );
            }

            $html = $body;

            $rfps = $this->normalizer->normalize(
                ['html' => $html],
                $scrapeData,
                ScrapeMethod::UI
            );

            $allRfps = $this->paginateSubsequentPages($targetUrl, $rfps, $html, $scrapeData, $headers);

            Log::info('BidNet Direct pages normalized.', [
                'url' => $targetUrl,
                'first_page_count' => count($rfps),
                'total_rfp_count' => count($allRfps),
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            return ScraperResult::success($allRfps, ScrapeMethod::UI, [
                'url' => $targetUrl,
                'count' => count($allRfps),
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

    /**
     * Launch browser to let user solve CAPTCHA visually and extract data.
     */
    protected function extractWithBrowser(string $targetUrl, RfpScrapeData $scrapeData): ScraperResult
    {
        $scriptPath = base_path('app/Services/Rfp/Platforms/BidNetDirect/Scripts/solve-and-scrape.cjs');

        if (! file_exists($scriptPath)) {
            return ScraperResult::failure("Browser solver script not found at {$scriptPath}", ScrapeMethod::UI);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'bidnet_out_');
        $headless = isset($scrapeData->options['headless']) ? ($scrapeData->options['headless'] ? 'true' : 'false') : 'false';
        $timeout = (int) ($scrapeData->options['browser_timeout'] ?? 180000);

        Log::info('Launching browser for BidNet Direct CAPTCHA resolution...', [
            'url' => $targetUrl,
            'headless' => $headless,
            'timeout' => $timeout,
        ]);

        $nodeBinary = trim((string) shell_exec('which node 2>/dev/null')) ?: 'node';

        $display = getenv('DISPLAY') ?: ':0';
        $wayland = getenv('WAYLAND_DISPLAY') ?: 'wayland-0';

        $maxPages = (int) ($scrapeData->options['max_pages'] ?? 50);

        $cmd = [
            $nodeBinary,
            $scriptPath,
            '--url', $targetUrl,
            '--headless', $headless,
            '--timeout', (string) $timeout,
            '--output', $tempFile,
            '--max-pages', (string) $maxPages,
        ];

        $wafToken = $scrapeData->options['waf_token']
            ?? Cache::get('bidnet_waf_token')
            ?? config('services.bidnet.waf_token');

        if (! empty($wafToken)) {
            $cmd[] = '--waf-token';
            $cmd[] = trim((string) $wafToken);
        }

        $processResult = Process::timeout($timeout / 1000 + 30)
            ->env([
                'DISPLAY' => $display,
                'WAYLAND_DISPLAY' => $wayland,
                'XAUTHORITY' => getenv('XAUTHORITY') ?: (getenv('HOME').'/.Xauthority'),
                'HOME' => getenv('HOME') ?: '/home/lenovo',
                'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
            ])
            ->run($cmd);

        if (! file_exists($tempFile) || filesize($tempFile) === 0) {
            $errorOutput = $processResult->errorOutput() ?: $processResult->output();
            Log::warning('BidNet Direct browser extraction produced no output.', [
                'exit_code' => $processResult->exitCode(),
                'error' => $errorOutput,
            ]);

            @unlink($tempFile);

            return ScraperResult::failure(
                'BidNet Direct browser verification did not complete. Error: '.($errorOutput ?: 'Process exited without output.'),
                ScrapeMethod::UI
            );
        }

        try {
            $jsonContent = file_get_contents($tempFile);
            $parsed = json_decode($jsonContent, true);
            @unlink($tempFile);

            if (! is_array($parsed) || empty($parsed['success']) || empty($parsed['html'])) {
                $err = $parsed['error'] ?? 'Unknown error during browser extraction';

                return ScraperResult::failure("BidNet Direct browser extraction failed: {$err}", ScrapeMethod::UI);
            }

            if (! empty($parsed['wafToken'])) {
                Cache::put('bidnet_waf_token', $parsed['wafToken'], now()->addHours(2));
                Log::info('BidNet Direct aws-waf-token captured and cached automatically.', [
                    'waf_token_length' => strlen($parsed['wafToken']),
                ]);
            }

            if (! empty($parsed['userAgent'])) {
                Cache::put('bidnet_user_agent', $parsed['userAgent'], now()->addHours(2));
            }

            $allRfps = [];

            if (! empty($parsed['htmlPages']) && is_array($parsed['htmlPages'])) {
                foreach ($parsed['htmlPages'] as $pageHtml) {
                    $pageRfps = $this->normalizer->normalize(
                        ['html' => $pageHtml],
                        $scrapeData,
                        ScrapeMethod::UI
                    );
                    $allRfps = array_merge($allRfps, $pageRfps);
                }
            } else {
                $rfps = $this->normalizer->normalize(
                    ['html' => $parsed['html']],
                    $scrapeData,
                    ScrapeMethod::UI
                );

                $userAgent = ! empty($parsed['userAgent'])
                    ? $parsed['userAgent']
                    : (Cache::get('bidnet_user_agent') ?: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');

                $browserHeaders = [
                    'User-Agent' => $userAgent,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => $targetUrl,
                ];

                $allRfps = $this->paginateSubsequentPages($targetUrl, $rfps, $parsed['html'], $scrapeData, $browserHeaders);
            }

            Log::info('BidNet Direct browser extraction normalized successfully.', [
                'total_rfp_count' => count($allRfps),
                'browser' => $parsed['browser'] ?? 'unknown',
            ]);

            return ScraperResult::success($allRfps, ScrapeMethod::UI, [
                'url' => $targetUrl,
                'source' => 'browser_interactive',
                'count' => count($allRfps),
                'browser' => $parsed['browser'] ?? 'unknown',
            ]);
        } catch (Throwable $e) {
            @unlink($tempFile);
            Log::error('BidNet Direct browser result parsing failed.', ['exception' => $e]);

            return ScraperResult::failure("Failed to process browser output: {$e->getMessage()}", ScrapeMethod::UI);
        }
    }

    /**
     * Paginate through subsequent listing pages using the valid session/token.
     *
     * @param  array<int, RfpData>  $firstPageRfps
     * @param  array<string, string>  $baseHeaders
     * @return array<int, RfpData>
     */
    protected function paginateSubsequentPages(
        string $targetUrl,
        array $firstPageRfps,
        string $firstPageHtml,
        RfpScrapeData $scrapeData,
        array $baseHeaders
    ): array {
        $allRfps = $firstPageRfps;
        $hasNextPage = str_contains($firstPageHtml, 'rel="next"') || str_contains($firstPageHtml, 'class="next"');
        $maxPages = (int) ($scrapeData->options['max_pages'] ?? 50);
        $currentPage = 1;

        while ($hasNextPage && $currentPage < $maxPages) {
            $currentPage++;
            $pageUrl = BidNetDirectConfig::buildListingUrl($targetUrl, $currentPage);

            $pageHeaders = $baseHeaders;
            $currentWafToken = $scrapeData->options['waf_token']
                ?? Cache::get('bidnet_waf_token')
                ?? config('services.bidnet.waf_token');

            if (! empty($currentWafToken)) {
                $trimmedToken = trim((string) $currentWafToken);
                $pageHeaders['Cookie'] = str_starts_with($trimmedToken, 'aws-waf-token=')
                    ? $trimmedToken
                    : "aws-waf-token={$trimmedToken}";
            }

            // Small delay between page requests
            usleep(250000); // 0.25s

            $pageResponse = Http::withHeaders($pageHeaders)->timeout(20)->get($pageUrl);

            if (! $pageResponse->successful()) {
                Log::warning("BidNet Direct page {$currentPage} request returned status {$pageResponse->status()}", ['url' => $pageUrl]);
                break;
            }

            $pageHtml = $pageResponse->body();
            $pageRfps = $this->normalizer->normalize(['html' => $pageHtml], $scrapeData, ScrapeMethod::UI);

            if (empty($pageRfps)) {
                Log::info("BidNet Direct page {$currentPage} returned 0 RFPs. Ending pagination.", ['url' => $pageUrl]);
                break;
            }

            $allRfps = array_merge($allRfps, $pageRfps);
            $hasNextPage = str_contains($pageHtml, 'rel="next"') || str_contains($pageHtml, 'class="next"');

            Log::info("BidNet Direct page {$currentPage} scraped.", [
                'page' => $currentPage,
                'page_rfps' => count($pageRfps),
                'total_so_far' => count($allRfps),
                'has_next' => $hasNextPage,
            ]);
        }

        return $allRfps;
    }
}
