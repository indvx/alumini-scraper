<?php

namespace App\Services\Rfp\Platforms\Bonfire;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\Bonfire\Strategies\BonfireAiStrategy;
use App\Services\Rfp\Platforms\Bonfire\Strategies\BonfireApiStrategy;
use App\Services\Rfp\Platforms\Bonfire\Strategies\BonfireUiStrategy;

class BonfireScraper implements RfpPlatformScraper
{
    public function __construct(
        protected BonfireApiStrategy $apiStrategy,
        protected BonfireUiStrategy $uiStrategy,
        protected BonfireAiStrategy $aiStrategy
    ) {}

    public function scrape(RfpScrapeData $scrapeData): ScraperResult
    {
        $strategies = [
            $this->apiStrategy,
            $this->uiStrategy,
            $this->aiStrategy,
        ];

        $errors = [];
        foreach ($strategies as $strategy) {
            if (! $strategy->supports($scrapeData)) {
                continue;
            }

            try {
                $result = $strategy->extract($scrapeData);
                if ($result->isSuccess() && $result->count() > 0) {
                    return $result;
                }

                if ($result->isFailure() && $result->errorMessage) {
                    $errors[] = $result->errorMessage;
                }
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        return ScraperResult::failure(
            'All Bonfire scraping strategies failed or returned no RFPs.',
            ScrapeMethod::AI,
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
