<?php

namespace App\Data\Rfp;

use App\Models\Institution;
use App\Models\RFPsPlatform;

class DiscoveryResult
{
    public function __construct(
        public ?string $portalUrl = null,
        public ?Institution $institution = null,
        public ?RFPsPlatform $platformRecord = null,
        public string $source = 'db'
    ) {}

    public function isSuccess(): bool
    {
        return ! empty($this->portalUrl);
    }
}
