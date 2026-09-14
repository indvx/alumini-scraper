<?php

namespace App\Services\Rfp\Contracts;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;

interface RfpNormalizer
{
    /**
     * @param  array<string, mixed>  $rawPayload
     * @return array<int, RfpData>
     */
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, string $source = 'api'): array;
}
