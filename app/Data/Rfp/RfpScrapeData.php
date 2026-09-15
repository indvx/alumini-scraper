<?php

namespace App\Data\Rfp;

use App\Enums\Rfp\RfpStatus;

class RfpScrapeData
{
    public function __construct(
        public string $universityName,
        public ?string $portalUrl = null,
        public RfpStatus $type = RfpStatus::OPEN,
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public ?int $institutionId = null,
        public ?int $platformId = null,
        public array $options = []
    ) {}
}
