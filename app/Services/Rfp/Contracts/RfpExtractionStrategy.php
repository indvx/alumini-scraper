<?php

namespace App\Services\Rfp\Contracts;

use App\Data\Rfp\RfpScrapeData;
use App\Data\Scraper\ScraperResult;

interface RfpExtractionStrategy
{
    public function extract(RfpScrapeData $scrapeData): ScraperResult;

    public function supports(RfpScrapeData $scrapeData): bool;
}
