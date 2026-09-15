<?php

namespace App\Services\Rfp\Platforms\Bonfire;

class BonfireConfig
{
    public const OPEN_SECTION_ENDPOINT = '/PublicPortal/getOpenPublicOpportunitiesSectionData';

    public const PAST_SECTION_ENDPOINT = '/PublicPortal/getPastPublicOpportunitiesSectionData';

    public static function getApiEndpoint(string $type): string
    {
        return strtolower($type) === 'past' || strtolower($type) === 'closed'
            ? self::PAST_SECTION_ENDPOINT
            : self::OPEN_SECTION_ENDPOINT;
    }
}
