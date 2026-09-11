<?php

namespace App\Services;

use App\Models\RFPsPlatform;
use Illuminate\Support\Str;

class AiPlatformGeneratorService
{
    /**
     * Parse natural language prompt and generate/create new RFP platform records in table.
     *
     * @return array{createdCount: int, createdPlatforms: array<RFPsPlatform>}
     */
    public function generateFromPrompt(string $prompt): array
    {
        $lowercasePrompt = strtolower($prompt);

        // Detect State / Location
        $states = [
            'california' => ['state' => 'California', 'city' => 'Sacramento', 'country' => 'United States'],
            'texas' => ['state' => 'Texas', 'city' => 'Austin', 'country' => 'United States'],
            'new york' => ['state' => 'New York', 'city' => 'Albany', 'country' => 'United States'],
            'florida' => ['state' => 'Florida', 'city' => 'Tallahassee', 'country' => 'United States'],
            'illinois' => ['state' => 'Illinois', 'city' => 'Springfield', 'country' => 'United States'],
            'pennsylvania' => ['state' => 'Pennsylvania', 'city' => 'Harrisburg', 'country' => 'United States'],
            'ohio' => ['state' => 'Ohio', 'city' => 'Columbus', 'country' => 'United States'],
            'georgia' => ['state' => 'Georgia', 'city' => 'Atlanta', 'country' => 'United States'],
            'washington' => ['state' => 'Washington', 'city' => 'Olympia', 'country' => 'United States'],
        ];

        $detectedLocation = ['state' => 'National', 'city' => 'Washington D.C.', 'country' => 'United States'];
        foreach ($states as $key => $loc) {
            if (str_contains($lowercasePrompt, $key)) {
                $detectedLocation = $loc;
                break;
            }
        }

        // Detect Institution Type
        $institutionType = 'Public Institution';
        if (str_contains($lowercasePrompt, 'university') || str_contains($lowercasePrompt, 'higher ed') || str_contains($lowercasePrompt, 'college')) {
            $institutionType = 'Public University System';
        } elseif (str_contains($lowercasePrompt, 'school') || str_contains($lowercasePrompt, 'k12') || str_contains($lowercasePrompt, 'district')) {
            $institutionType = 'School District';
        } elseif (str_contains($lowercasePrompt, 'government') || str_contains($lowercasePrompt, 'state') || str_contains($lowercasePrompt, 'federal')) {
            $institutionType = 'Government Agency';
        }

        // Detect Platform Type
        $platformType = 'Procurement Portal';
        if (str_contains($lowercasePrompt, 'vendor') || str_contains($lowercasePrompt, 'supplier')) {
            $platformType = 'Vendor Portal';
        } elseif (str_contains($lowercasePrompt, 'bidding') || str_contains($lowercasePrompt, 'bid')) {
            $platformType = 'Bidding Network';
        } elseif (str_contains($lowercasePrompt, 'exchange') || str_contains($lowercasePrompt, 'marketplace')) {
            $platformType = 'Procurement Exchange';
        }

        $requiresLogin = str_contains($lowercasePrompt, 'login') || str_contains($lowercasePrompt, 'account') || str_contains($lowercasePrompt, 'private');
        $isPublic = ! str_contains($lowercasePrompt, 'private');

        // Extract key domain prefix
        $cleanPromptKeywords = array_filter(
            explode(' ', preg_replace('/[^\w\s]/', '', $prompt)),
            fn ($word) => ! in_array(strtolower($word), ['find', 'search', 'show', 'me', 'active', 'inactive', 'public', 'private', 'platform', 'platforms', 'portals', 'for', 'the', 'a', 'an', 'in', 'of', 'and', 'or', 'with', 'create', 'generate'])
        );
        $subjectKey = implode(' ', array_slice($cleanPromptKeywords, 0, 3)) ?: 'Higher Education';
        $slug = Str::slug($subjectKey);

        // Define platform candidates to create
        $candidates = [
            [
                'name' => titlecase("{$subjectKey} Procurement Exchange"),
                'domain' => "{$slug}-procure.org",
                'url' => "https://{$slug}-procure.org",
                'platform_type' => $platformType,
                'institution_type' => $institutionType,
                'coverage' => $detectedLocation['state'] === 'National' ? 'National' : 'Statewide',
                'city' => $detectedLocation['city'],
                'state' => $detectedLocation['state'],
                'country' => $detectedLocation['country'],
                'is_public' => $isPublic,
                'requires_login' => $requiresLogin,
                'status' => 'active',
                'last_checked' => now(),
            ],
            [
                'name' => titlecase("{$detectedLocation['state']} {$institutionType} Vendor Network"),
                'domain' => Str::slug("{$detectedLocation['state']}-{$institutionType}-vendor").'.gov',
                'url' => 'https://'.Str::slug("{$detectedLocation['state']}-{$institutionType}-vendor").'.gov',
                'platform_type' => 'Vendor Portal',
                'institution_type' => $institutionType,
                'coverage' => 'Statewide',
                'city' => $detectedLocation['city'],
                'state' => $detectedLocation['state'],
                'country' => $detectedLocation['country'],
                'is_public' => true,
                'requires_login' => $requiresLogin,
                'status' => 'active',
                'last_checked' => now(),
            ],
            [
                'name' => titlecase("{$subjectKey} Solicitations & Contracts Board"),
                'domain' => "{$slug}-bids.com",
                'url' => "https://{$slug}-bids.com",
                'platform_type' => 'Bidding Network',
                'institution_type' => $institutionType,
                'coverage' => 'Regional',
                'city' => $detectedLocation['city'],
                'state' => $detectedLocation['state'],
                'country' => $detectedLocation['country'],
                'is_public' => $isPublic,
                'requires_login' => false,
                'status' => 'active',
                'last_checked' => now(),
            ],
        ];

        $createdPlatforms = [];
        foreach ($candidates as $candidate) {
            $existing = RFPsPlatform::where('name', $candidate['name'])
                ->orWhere('domain', $candidate['domain'])
                ->first();

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
