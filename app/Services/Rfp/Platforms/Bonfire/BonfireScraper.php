<?php

namespace App\Services\Rfp\Platforms\Bonfire;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\Bonfire\Strategies\BonfireApiStrategy;

class BonfireScraper implements RfpPlatformScraper
{
    public function __construct(
        protected BonfireApiStrategy $apiStrategy,
    ) {}

    public function scrape(RfpScrapeData $scrapeData): ScraperResult
    {
        $strategy = $this->apiStrategy;
        $errors = [];
        if (! $strategy->supports($scrapeData)) {
            return ScraperResult::failure(
                'Bonfire API strategy does not support the given platform.',
                ScrapeMethod::API,
                [
                    'errors' => [],
                ]
            );
        }

        try {
            $result = $strategy->extract($scrapeData);
            if ($result->isSuccess() && $result->count() > 0) {
                return $result;
            }

            if ($result->isFailure() && $result->errorMessage) {
                $errors = [$result->errorMessage];
            }
        } catch (\Throwable $e) {
            $errors = [$e->getMessage()];
        }

        return ScraperResult::failure(
            'Bonfire API strategy failed or returned no RFPs.',
            ScrapeMethod::API,
            [
                'errors' => $errors,
            ]
        );
    }

    public function supports(string $platform): bool
    {
        $platform = strtolower(trim($platform));

        return $platform === 'bonfire'
            || str_contains($platform, 'bonfirehub.com');
    }

    public function getPlatformName(): string
    {
        return 'Bonfire';
    }
}
