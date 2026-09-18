<?php

namespace App\Services\Rfp\Platforms\OpenGov;

class OpenGovConfig
{
    public const BASE_API_URL = 'https://api.procurement.opengov.com/api/v1';

    public const BASE_PORTAL_URL = 'https://procurement.opengov.com/portal';

    public static function getPublicProjectsEndpoint(string $govCode): string
    {
        $code = strtolower(trim($govCode));

        return self::BASE_API_URL . "/government/{$code}/project/public";
    }

    public static function buildOpportunityUrl(string $govCode, string|int $projectId): string
    {
        $code = strtolower(trim($govCode));

        return self::BASE_PORTAL_URL . "/{$code}/projects/{$projectId}";
    }

    public static function extractGovCode(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        if (preg_match('/procurement\.opengov\.com\/portal\/([^\/\?#]+)/i', $url, $matches)) {
            return strtolower($matches[1]);
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path && preg_match('/^\/?portal\/([^\/\?#]+)/i', $path, $matches)) {
            return strtolower($matches[1]);
        }

        if (! str_contains($url, '/') && ! str_contains($url, '.')) {
            return strtolower($url);
        }

        return null;
    }

    public static function extractProjectId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (preg_match('/procurement\.opengov\.com\/portal\/[^\/]+\/projects\/([^\/\?#]+)/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
