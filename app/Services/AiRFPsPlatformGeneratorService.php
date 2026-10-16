<?php

namespace App\Services;

use App\Models\RFPsPlatform;
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
                                    Find procurement platforms used by educational institutions in {$country}, {$state}.

                                Search the web extensively.

                                Focus on:
                                    - Universities
                                    - Colleges
                                    - Community colleges
                                    - Schools
                                    - School districts

                                Find:
                                    1. {$country} government/education procurement portals
                                    2. Third-party procurement platforms used by {$country} institutions
                                    3. Institution-specific procurement portals

                                IMPORTANT:
                                    - Do NOT return individual RFPs.
                                    - Find the platforms/websites where RFPs, bids, tenders,
                                    solicitations, or procurement opportunities are published.
                                    - Search multiple {$country} educational institutions.
                                    - Verify every platform using an institutional or government
                                    evidence URL.
                                    - Deduplicate platforms.
                                    - Do not stop after finding only a few platforms.
                                    - Continue searching until additional searches produce
                                    no materially new platforms.

                                For each platform return:
                                    name
                                    domain
                                    url
                                    platform_type
                                    country
                                    state
                                    city
                                    institution_type
                                    coverage
                                    is_public
                                    requires_login
                                    evidence_url
                                    institutions_using_it
                                    confidence

                                Return JSON only.
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

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $name = $candidate['name'] ?? null;
            $domain = $candidate['domain'] ?? null;

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

            if (! $existing) {
                $createdPlatforms[] = RFPsPlatform::create($candidate);
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
