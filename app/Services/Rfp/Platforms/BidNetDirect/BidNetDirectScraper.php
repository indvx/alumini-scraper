<?php

namespace App\Services\Rfp\Platforms\BidNetDirect;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\BidNetDirect\Strategies\BidNetDirectUrlStrategy;
use Throwable;

class BidNetDirectScraper implements RfpPlatformScraper
{
    public function __construct(
        protected BidNetDirectUrlStrategy $urlStrategy
    ) {}

    public function scrape(RfpScrapeData $scrapeData): ScraperResult
    {
        $strategy = $this->urlStrategy;
        $errors = [];

        if (! $strategy->supports($scrapeData)) {
            return ScraperResult::failure(
                'BidNet Direct strategy does not support the given platform or URL.',
                ScrapeMethod::UI,
                [
                    'errors' => [
                        'BidNet Direct strategy does not support the given platform or URL.',
                    ],
                ]
            );
        }

        try {
            $result = $strategy->extract($scrapeData);
            if ($result->isSuccess()) {
                return $result;
            }

            if ($result->isFailure() && $result->errorMessage) {
                $errors[] = $result->errorMessage;
            }
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return ScraperResult::failure(
            'BidNet Direct strategy failed or returned no RFPs.',
            ScrapeMethod::UI,
            [
                'errors' => $errors,
            ]
        );
    }

    public function supports(string $platform): bool
    {
        $platform = strtolower(trim($platform));

        return $platform === 'bidnet direct'
            || $platform === 'bidnetdirect'
            || $platform === 'bidnet'
            || str_contains($platform, 'bidnetdirect.com');
    }

    public function getPlatformName(): string
    {
        return 'BidNet Direct';
    }
}
