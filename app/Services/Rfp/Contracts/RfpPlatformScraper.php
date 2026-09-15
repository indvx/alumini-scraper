<?php

namespace App\Services\Rfp\Contracts;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;

interface RfpPlatformScraper
{
    public function scrape(RfpScrapeData $scrapeData): ScraperResult;

    public function supports(string $platform): bool;

    public function getPlatformName(): string;
}
