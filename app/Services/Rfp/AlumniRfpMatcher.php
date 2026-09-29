<?php

namespace App\Services\Rfp;

use App\Data\Rfp\RfpData;

final class AlumniRfpMatcher
{
    private const ALUMNI_TERMS = [
        'alumni',
        'alumnus',
        'alumna',
        'alumnae',
        'former student',
        'graduate relations',
        'graduate engagement',
    ];

    private const ALUMNI_TITLE_TERMS = [
        ...self::ALUMNI_TERMS,
        'alumni association',
        'alumni network',
        'alumni database',
        'alumni crm',
        'alumni management',
        'alumni portal',
        'alumni platform',
        'alumni directory',
        'alumni fundraising',
        'alumni giving',
        'alumni reunion',
    ];

    private const SERVICE_TERMS = [
        'crm',
        'database',
        'platform',
        'portal',
        'software',
        'application',
        'system',
        'technology',
        'directory',
        'management',
        'relationship management',
        'engagement',
        'communication',
        'communications',
        'outreach',
        'relations',
        'fundraising',
        'fund raising',
        'giving',
        'donor',
        'event',
        'events',
        'reunion',
        'reunions',
        'services',
        'support',
        'consulting',
        'implementation',
        'development',
        'integration',
        'maintenance',
        'marketing',
    ];

    private const ENGAGEMENT_AUDIENCES = [
        'student engagement',
        'student experience',
        'student success',
        'student affairs',
        'student activities',
        'student involvement',
        'student communication',
        'student communications',

        'employee engagement',
        'employee experience',
        'employee communication',
        'employee communications',

        'faculty engagement',
        'faculty experience',
        'faculty communication',
        'faculty communications',

        'staff engagement',
        'staff experience',
        'staff communication',
        'staff communications',

        'patient engagement',
        'patient experience',
        'patient communication',
        'patient communications',

        'member engagement',
        'member experience',
        'member communication',
        'member communications',

        'community engagement',
        'community outreach',
        'constituent engagement',

        'crm',
        'customer relationship management',
        'customer engagement',
        'customer experience',
        'customer communication',
        'customer communications',

        'event management software',
        'event management platform',
        'event management system',
        'event management solution',
        'event management application',
        'event management portal',
        'event management mobile application',
        'event management mobile app',
        'event management digital platform',
        'event management digital solution',
        'event management services',
        'event management support',
    ];

    private const TECHNOLOGY_TERMS = [
        'technology',
        'engagement platform',
        'engagement software',
        'engagement system',
        'engagement solution',
        'experience platform',
        'experience software',
        'experience system',
        'experience solution',
        'communication platform',
        'communications platform',
        'communication software',
        'communications software',
        'communication system',
        'communications system',
        'software',
        'platform',
        'system',
        'solution',
        'application',
        'portal',
        'mobile application',
        'mobile app',
        'digital platform',
        'digital solution',
        'services',
    ];

    private const ADVANCEMENT_TERMS = [
        'institutional advancement',
        'advancement services',
        'advancement office',
        'advancement department',
        'advancement crm',
        'advancement platform',
        'advancement system',
        'constituent relationship management',
        'constituent management',
        'constituent engagement',
        'constituent crm',
        'constituent database',
        'constituent platform',
    ];

    private const FUNDRAISING_TERMS = [
        'fundraising',
        'fund raising',
        'development campaign',
        'annual giving',
        'major gifts',
        'planned giving',
        'donor management',
        'donor engagement',
    ];

    private const INSTITUTIONAL_TERMS = [
        'university',
        'universities',
        'college',
        'colleges',
        'higher education',
        'academic institution',
        'educational institution',
    ];

    public function isRelevant(RfpData $rfp): bool
    {
        $title = $this->normalize($rfp->title);

        $text = $this->normalize(
            implode(' ', [
                $rfp->title,
                $rfp->description,
                $rfp->department,
            ])
        );

        return $this->matches($title, self::ALUMNI_TITLE_TERMS)
            || (
                $this->matches($text, self::ALUMNI_TERMS)
                && $this->matches($text, self::SERVICE_TERMS)
            )
            || (
                $this->matches($text, self::ENGAGEMENT_AUDIENCES)
                && $this->matches($text, self::TECHNOLOGY_TERMS)
            )
            || $this->hasAdvancementContext($text);
    }

    private function hasAdvancementContext(string $text): bool
    {
        $institutional = $this->matches($text, self::INSTITUTIONAL_TERMS);
        $relationship = $this->matches($text, [
            'crm',
            'database',
            'platform',
            'relationship management',
            'engagement',
            'constituent management',
        ]);

        if (! $institutional || ! $relationship) {
            return false;
        }

        return $this->matches($text, self::ADVANCEMENT_TERMS)
            || (
                $this->matches($text, self::FUNDRAISING_TERMS)
                && $this->matches($text, [
                    'donor',
                    'constituent',
                    'advancement',
                    'giving',
                    'development',
                ])
            );
    }

    private function normalize(?string $text): string
    {
        $text = strtolower($text ?? '');
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '';

        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    private function matches(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($text, $this->normalize($term))) {
                return true;
            }
        }

        return false;
    }
}
