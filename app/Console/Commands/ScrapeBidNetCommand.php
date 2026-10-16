<?php

namespace App\Console\Commands;

use App\Data\Rfp\RfpScrapeData;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Models\RFPsPlatform;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectConfig;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectNormalizer;
use App\Services\Rfp\RfpPersistenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScrapeBidNetCommand extends Command
{
    protected $signature = 'bidnet:scrape
        {--pages=1 : Number of listing pages to scrape}
        {--start-page=1 : First listing page}
        {--delay=2 : Seconds to wait between pages}
        {--listing-url=https://www.bidnetdirect.com/alabama/solicitations/closed-bids : Listing URL}';

    protected $description = 'Scrape publicly accessible BidNet Direct solicitation listings and save to database';

    public function handle(
        BidNetDirectNormalizer $normalizer,
        RfpPersistenceService $persistenceService
    ): int {
        $pages = max(1, (int) $this->option('pages'));
        $startPage = max(1, (int) $this->option('start-page'));
        $delay = max(1, (int) $this->option('delay'));
        $baseUrl = rtrim((string) $this->option('listing-url'), '/');
        if (empty($baseUrl)) {
            $baseUrl = BidNetDirectConfig::getPublicPortalUrl();
        }

        $this->info('Starting BidNet Direct scraper (listing only)...');
        $this->line("Listing URL: {$baseUrl}");
        $this->line("Pages: {$startPage} to " . ($startPage + $pages - 1));

        $platformRecord = RFPsPlatform::where('name', 'like', '%BidNet%')
            ->orWhere('platform_type', 'like', '%BidNet%')
            ->first();

        $processed = 0;
        $failed = 0;

        for ($page = $startPage; $page < $startPage + $pages; $page++) {
            sleep($delay);
            $listingUrl = BidNetDirectConfig::buildListingUrl($baseUrl, $page);

            $status = str_contains($listingUrl, 'closed')
                ? RfpStatus::CLOSED
                : RfpStatus::OPEN;

            $this->newLine();
            $this->info("Scraping listing page {$page}: {$listingUrl}");

            try {
                $html = $this->fetchHtml($listingUrl);

                if ($html === null) {
                    $failed++;

                    $this->warn(
                        "Unable to retrieve listing page {$page}. Skipping."
                    );

                    continue;
                }

                $scrapeData = new RfpScrapeData(
                    universityName: 'BidNet Direct',
                    portalUrl: $listingUrl,
                    type: $status,
                    platformId: $platformRecord?->id

                );

                $rfps = $normalizer->normalizeHtml($html, $scrapeData, ScrapeMethod::UI);

                if (empty($rfps)) {
                    $this->warn(
                        'No solicitations found on page. The page may have changed, '
                            . 'returned a challenge page, or contained no results.'
                    );

                    Log::warning('BidNet listing contains no solicitations', [
                        'url' => $listingUrl,
                    ]);

                    continue;
                }

                $this->info('Found ' . count($rfps) . ' solicitation(s).');
                $this->info(json_encode($rfps, JSON_PRETTY_PRINT));

                foreach ($rfps as $rfp) {
                    try {
                        $this->line('Saving/updating RFP: '.($rfp->title ?: $rfp->projectId));
                        $persistenceService->save($rfp);
                        $processed++;
                    } catch (Throwable $e) {
                        $failed++;
                        $this->error('Failed to save/update an RFP: '.$e->getMessage());

                        Log::error('BidNet RFP persistence failed', [
                            'project_id' => $rfp->projectId,
                            'title' => $rfp->title,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if ($delay > 0 && $page < $startPage + $pages - 1) {
                    sleep($delay);
                }
            } catch (Throwable $e) {
                $failed++;
                $this->error("Listing page {$page} failed: " . $e->getMessage());

                Log::error('BidNet listing-page scrape failed', [
                    'url' => $listingUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info('Scraping run finished.');
        $this->line("Records processed: {$processed}");
        $this->line("Errors: {$failed}");

        return self::SUCCESS;
    }

    /**
     * Download an HTML page.
     */
    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->connectTimeout(10)
                ->timeout(30)
                ->get($url);

            $body = $response->body();

            $isCaptcha = strtolower(
                $response->header('x-amzn-waf-action', '')
            ) === 'captcha';

            if ($isCaptcha) {
                Log::warning('BidNetDirect CAPTCHA encountered', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                $this->warn(
                    'CAPTCHA detected. Skipping this request.'
                );

                return null;
            }

            if (! $response->successful()) {
                Log::warning('BidNetDirect request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            }

            if (trim($body) === '') {
                Log::warning('BidNetDirect returned an empty response', [
                    'url' => $url,
                ]);

                return null;
            }

            return $body;
        } catch (\Throwable $e) {
            Log::error('BidNetDirect request exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
