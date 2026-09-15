<?php

namespace App\Services\Rfp\Discovery;

use App\Data\Rfp\DiscoveryResult;
use App\Models\Institution;
use App\Models\RFPsPlatform;
use App\Services\OpenAIService;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;

class ProcurementPlatformDiscoveryService
{
    public function __construct(
        protected OpenAIService $openAIService
    ) {}

    /**
     * Discover procurement portal URL and associated database models.
     * Checks DB first. External lookup / OpenAI is called only if allowed and missing from DB.
     *
     * @throws ItemNotFoundException
     */
    public function discoverPortal(
        string $universityName,
        string $platformName = 'Bonfire',
        ?Institution $institution = null,
        ?RFPsPlatform $platformRecord = null,
        bool $allowExternalLookup = true
    ): DiscoveryResult {
        $universityName = trim($universityName);
        $platformName = trim($platformName);

        if ($institution || $platformRecord) {
            $portalUrl = $this->resolveUrlFromModels($institution, $platformRecord, $platformName);
            if (! empty($portalUrl)) {
                return new DiscoveryResult($portalUrl, $institution, $platformRecord, 'db');
            }
        }

        $dbResult = $this->findPortalInDatabase($universityName, $platformName);
        if ($dbResult->isSuccess()) {
            return $dbResult;
        }

        if (! $allowExternalLookup) {
            throw new ItemNotFoundException("Procurement portal URL for '{$universityName}' on platform '{$platformName}' was not found.");
        }

        $prompt = <<<PROMPT
            Find the official {$platformName} procurement/RFP portal for:
            
                Institution: {$universityName}
            
            Return ONLY valid JSON in this exact format:
                {
                    "portal_url": "https://...",
                    "institution_name": "...",
                    "platform": "{$platformName}",
                    "confidence": 0
                }

            Rules:
                - portal_url must be the canonical, currently active {$platformName} procurement/RFP portal URL used by the institution.
                - The URL must point directly to the institution's procurement/RFP portal on the {$platformName} platform.
                - DO NOT construct, guess, infer, or generate the URL from the institution name.
                - DO NOT assume the subdomain follows the institution's full name.
                - Verify the exact portal URL from an official institution procurement page, official solicitation document, or the {$platformName} platform itself.
                - Prefer the exact canonical portal URL explicitly referenced by the institution.
                - If an official institution page redirects to a {$platformName} portal, return the final canonical {$platformName} portal URL, not the institution page.
                - Preserve the exact verified subdomain. For example, if the verified URL is "https://tsc.bonfirehub.com/", do not replace it with "https://texassouthmostcollege.bonfirehub.com/".
                - Do not return the institution homepage.
                - Do not return an official procurement information page if it is not the actual {$platformName} portal.
                - portal_url must normally use HTTPS.
                - Do not return an explanation.
                - Do not return markdown.
                - Do not return any text outside the JSON.
                - If you cannot verify the exact portal URL, return:
                    {
                        "portal_url": null,
                        "institution_name": "{$universityName}",
                        "platform": "{$platformName}",
                        "confidence": 0
                    }
        PROMPT;

        $aiResult = $this->openAIService->prompt($prompt);
        $portalUrl = $this->extractPortalUrlFromAiResult($aiResult);
        if (empty($portalUrl)) {
            throw new ItemNotFoundException("Procurement portal URL for '{$universityName}' on platform '{$platformName}' was not found.");
        }

        return $this->savePortalUrlToDatabase($universityName, $platformName, $portalUrl);
    }

    public function discoverPortalUrl(string $universityName, string $platformName = 'Bonfire'): string
    {
        $result = $this->discoverPortal($universityName, $platformName);
        if (! $result->isSuccess()) {
            throw new ItemNotFoundException("Procurement portal URL for '{$universityName}' on platform '{$platformName}' was not found.");
        }

        return $result->portalUrl;
    }

    public function findPortalInDatabase(string $universityName, string $platformName): DiscoveryResult
    {
        $universityName = trim($universityName);
        $platformName = trim($platformName);
        $institution = Institution::where('name', 'like', "%{$universityName}%")
            ->orWhere('name', 'like', '%' . Str::slug($universityName, ' ') . '%')
            ->first();
        $platformRecord = null;
        $portalUrl = null;

        if ($institution) {
            $institution->loadMissing('rfpPlatforms');
            foreach ($institution->rfpPlatforms as $platform) {
                $matchesPlatform = str_contains(strtolower((string) $platform->name), strtolower($platformName)) ||
                    str_contains(strtolower((string) $platform->platform_type), strtolower($platformName)) ||
                    str_contains(strtolower((string) ($platform->pivot->source_url ?? '')), strtolower($platformName));

                if ($matchesPlatform) {
                    $platformRecord = $platform;
                    $portalUrl = $platform->pivot->source_url ?? $platform->url;
                    if (! empty($portalUrl)) {
                        break;
                    }
                }
            }
        }

        if (empty($portalUrl)) {
            $platformRecord = RFPsPlatform::where(function ($q) use ($universityName) {
                $q->where('name', 'like', "%{$universityName}%")
                    ->orWhere('url', 'like', "%{$universityName}%")
                    ->orWhere('domain', 'like', "%{$universityName}%");
            })->where(function ($q) use ($platformName) {
                $q->where('platform_type', 'like', "%{$platformName}%")
                    ->orWhere('name', 'like', "%{$platformName}%");
            })->first();

            if ($platformRecord) {
                $portalUrl = $platformRecord->url;
                if (! $institution) {
                    $institution = $platformRecord->rfpInstitutions()->first();
                }
            }
        }

        return new DiscoveryResult(
            portalUrl: $portalUrl,
            institution: $institution,
            platformRecord: $platformRecord,
            source: 'db'
        );
    }

    protected function resolveUrlFromModels(?Institution $institution, ?RFPsPlatform $platformRecord, string $platformName): ?string
    {
        if ($institution && $platformRecord) {
            $pivot = $institution->rfpPlatforms()->where('rfps_platform_id', $platformRecord->id)->first()?->pivot;

            return $pivot?->source_url;
        }
        if ($platformRecord?->url) {
            return $platformRecord->url;
        }
        if ($institution) {
            $institution->loadMissing('rfpPlatforms');
            foreach ($institution->rfpPlatforms as $platform) {
                if (
                    str_contains(strtolower((string) $platform->platform_type), strtolower($platformName)) ||
                    str_contains(strtolower((string) $platform->name), strtolower($platformName))
                ) {
                    return $platform->pivot->source_url ?? $platform->url;
                }
            }
        }

        return null;
    }

    protected function savePortalUrlToDatabase(string $universityName, string $platformName, string $portalUrl): DiscoveryResult
    {
        try {
            $host = parse_url($portalUrl, PHP_URL_HOST) ?? $portalUrl;
            $institution = Institution::where('name', 'like', "%{$universityName}%")->first();
            if (! $institution) {
                $institution = Institution::create(['name' => $universityName, 'type' => 'university']);
            }
            $platformRecord = RFPsPlatform::where('name', $platformName)->first();
            $relationExists = $institution->rfpPlatforms()->where('rfps_platform_id', $platformRecord->id)->exists();
            if (! $relationExists) {
                $institution->rfpPlatforms()->attach(
                    $platformRecord->id,
                    [
                        'confidence' => 100,
                        'status' => 'active',
                        'discovery_method' => 'openai_prompt',
                        'source_title' => "{$universityName} {$platformName} Procurement Portal",
                        'source_url' => $portalUrl,
                        'first_verified_at' => now(),
                        'last_verified_at' => now(),
                    ]
                );
            }

            return new DiscoveryResult($portalUrl, $institution, $platformRecord, 'openai');
        } catch (\Throwable $e) {
            return new DiscoveryResult($portalUrl, source: 'fallback');
        }
    }

    protected function extractPortalUrlFromAiResult(string $text): ?string
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $data = json_decode($text, true);
        if (! is_array($data)) {
            return null;
        }
        $url = $data['portal_url'] ?? null;
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return rtrim($url, '/');
    }
}
