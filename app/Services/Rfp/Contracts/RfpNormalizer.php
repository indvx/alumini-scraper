<?php

namespace App\Services\Rfp\Contracts;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Enums\Scraper\ScrapeMethod;

interface RfpNormalizer
{
    /**
     * @param  array<string, mixed>  $rawPayload
     * @return array<int, RfpData>
     */
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, ScrapeMethod $source): array;
}
