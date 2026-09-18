<?php

namespace App\Services\Rfp\Platforms\OpenGov;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\OpenGov\Strategies\OpenGovApiStrategy;

class OpenGovScraper implements RfpPlatformScraper
{
    public function __construct(
        protected OpenGovApiStrategy $apiStrategy,
    ) {}

    public function scrape(RfpScrapeData $scrapeData): ScraperResult
    {
        $strategy = $this->apiStrategy;
        $errors = [];

        if (! $strategy->supports($scrapeData)) {
            return ScraperResult::failure(
                'OpenGov API strategy does not support the given platform or URL.',
                ScrapeMethod::API,
                [
                    'errors' => [
                        'OpenGov API strategy does not support the given platform or URL.',
                    ],
                ]
            );
        }

        try {
            $result = $strategy->extract($scrapeData);
            dd($result);
            if ($result->isSuccess() && $result->count() > 0) {
                return $result;
            }

            if ($result->isFailure() && $result->errorMessage) {
                $errors[] = $result->errorMessage;
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        dd($errors);
        return ScraperResult::failure(
            'OpenGov API strategy failed or returned no RFPs.',
            ScrapeMethod::API,
            [
                'errors' => $errors,
            ]
        );
    }

    public function supports(string $platform): bool
    {
        $platform = strtolower(trim($platform));

        return $platform === 'opengov'
            || str_contains($platform, 'opengov.com')
            || str_contains($platform, 'procurement.opengov.com');
    }

    public function getPlatformName(): string
    {
        return 'OpenGov';
    }
}
