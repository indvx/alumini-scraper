<?php

namespace App\Services;

use App\Models\RFPsPlatform;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiRFPsPlatformGeneratorService
{
    public function __construct(protected OpenAIService $aiService) {}

    /**
     * generate/create new RFP platform records in table.
     *
     * @return array{createdCount: int, createdPlatforms: array<RFPsPlatform>}
     */
    public function generateFromPrompt(string $country, string $state, ?string $city = null): array
    {

        $location = implode(', ', array_filter([
            $country,
            $state,
            $city,
        ]));

        $response = $this->aiService->client()->responses()->create([
            'model' => 'gpt-5.6-luna',

            'tools' => [
                [
                    'type' => 'web_search',
                ],
            ],

            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => <<<PROMPT
                                Find verified procurement platforms used by educational institutions in "{$location}".

                                Perform no more than 8 web searches.

                                Focus on:
                                    - Universities
                                    - Colleges
                                    - Community colleges
                                    - Schools
                                    - School districts

                                Find:
                                    1. Government procurement portals
                                    2. Education procurement portals
                                    3. Major third-party procurement platforms
                                    4. Institution-specific procurement portals

                                IMPORTANT:
                                    - Find platforms, NOT individual RFPs.
                                    - A platform must be a website/system where procurement opportunities,
                                    tenders, bids, solicitations, or RFPs are published.
                                    - Verify each platform using a government, educational institution,
                                    or other authoritative evidence URL.
                                    - Deduplicate platforms.
                                    - Return a maximum of 15 platforms.
                                    - Prefer high-confidence platforms over exhaustive low-confidence results.
                                    - Do not perform additional searches after the 8-search limit.

                                For each platform return exactly:

                                {
                                    "name": "string",
                                    "domain": "string",
                                    "url": "string",
                                    "platform_type": "string",
                                    "country": "string",
                                    "state": "string|null",
                                    "city": "string|null",
                                    "institution_type": "string",
                                    "coverage": "string",
                                    "is_public": true,
                                    "requires_login": false,
                                    "evidence_url": "string",
                                    "institutions_using_it": ["maximum 5 institutions"],
                                    "confidence": "high"
                                }

                                Rules:
                                    - is_public must be boolean true/false.
                                    - requires_login must be boolean true/false.
                                    - Never put explanations in is_public or requires_login.
                                    - institutions_using_it must contain at most 5 institutions.
                                    - confidence must be "high", "medium", or "low".
                                    - Return JSON only.
                            PROMPT
                        ],
                    ],
                ],
            ],
        ]);

        $rawText = $response->outputText ?? '';

        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
        $candidates = json_decode($cleanJson, true);

        if (! is_array($candidates)) {
            $candidates = [];
        }

        $createdPlatforms = [];

        $formatString = function (mixed $value): ?string {
            if (is_array($value)) {
                $filtered = array_filter($value, fn($v) => is_scalar($v) && trim((string) $v) !== '');
                return ! empty($filtered) ? implode(', ', $filtered) : null;
            }

            if (is_scalar($value)) {
                $str = trim((string) $value);
                return $str !== '' ? $str : null;
            }

            return null;
        };

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $name = $formatString($candidate['name'] ?? null);
            $domain = $formatString($candidate['domain'] ?? null);

            if (! $name && ! $domain) {
                continue;
            }

            $existing = RFPsPlatform::where(function ($query) use ($name, $domain) {
                if ($name) {
                    $query->where('name', $name);
                }
                if ($domain) {
                    $query->orWhere('domain', $domain);
                }
            })->first();

            Log::info('Existing platform', [
                'existing' => $existing,
                'candidate' => $candidate,
            ]);

            if (! $existing) {
                $platformData = [
                    'name' => $name,
                    'domain' => $domain,
                    'url' => $formatString($candidate['url'] ?? null),
                    'platform_type' => $formatString($candidate['platform_type'] ?? null),
                    'country' => $formatString($candidate['country'] ?? null),
                    'state' => $formatString($candidate['state'] ?? null),
                    'city' => $formatString($candidate['city'] ?? null),
                    'institution_type' => $formatString($candidate['institution_type'] ?? null),
                    'coverage' => $formatString($candidate['coverage'] ?? null),
                    'is_public' => isset($candidate['is_public']) ? filter_var($candidate['is_public'], FILTER_VALIDATE_BOOLEAN) : false,
                    'requires_login' => isset($candidate['requires_login']) ? filter_var($candidate['requires_login'], FILTER_VALIDATE_BOOLEAN) : false,
                ];

                $createdPlatforms[] = RFPsPlatform::create($platformData);
            }
        }

        return [
            'createdCount' => count($createdPlatforms),
            'createdPlatforms' => $createdPlatforms,
        ];
    }
}

if (! function_exists('App\Services\titlecase')) {
    function titlecase(string $value): string
    {
        return Str::title($value);
    }
}
