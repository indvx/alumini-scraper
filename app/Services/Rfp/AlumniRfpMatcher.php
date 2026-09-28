<?php

namespace App\Services\Rfp;

use App\Data\Rfp\RfpData;

final class AlumniRfpMatcher
{
    public function isRelevant(RfpData $rfp): bool
    {
        $title = $this->normalize($rfp->title);
        $description = $this->normalize($rfp->description);
        $department = $this->normalize($rfp->department);

        $text = trim(
            $title . ' ' .
                $description . ' ' .
                $department
        );

        if ($this->hasStrongAlumniTitle($title)) {
            return true;
        }

        if ($this->hasAlumniTerm($text)) {
            return $this->hasAlumniServiceContext($text);
        }

        if ($this->hasEngagementTechnology($text)) {
            return true;
        }

        if ($this->hasStrongAdvancementContext($text)) {
            return true;
        }

        return false;
    }

    private function hasStrongAlumniTitle(string $title): bool
    {
        return $this->hasAnyMatch($title, [
            'alumni',
            'alumnus',
            'alumna',
            'alumnae',
            'former students',
            'former student',
            'graduate relations',
            'alumni relations',
            'alumni engagement',
            'alumni affairs',
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
            'alumni reunions',
            'graduate engagement',
            'graduate relations',
        ]);
    }

    private function hasAlumniTerm(string $text): bool
    {
        return $this->hasAnyMatch($text, [
            'alumni',
            'alumnus',
            'alumna',
            'alumnae',
            'former student',
            'former students',
            'graduate relations',
            'graduate engagement',
        ]);
    }

    private function hasAlumniServiceContext(string $text): bool
    {
        return $this->hasAnyMatch($text, [
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
            'management system',
            'relationship management',
            'engagement',
            'communications',
            'communication',
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
        ]);
    }

    private function hasEngagementTechnology(string $text): bool
    {
        $hasAudience = $this->hasAnyMatch($text, [
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
        ]);

        if (! $hasAudience) {
            return false;
        }

        return $this->hasAnyMatch($text, [
            'technology',
            'technology platform',
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
        ]);
    }


    private function hasStrongAdvancementContext(string $text): bool
    {
        $hasAdvancement = $this->hasAnyMatch($text, [
            'institutional advancement',
            'advancement services',
            'advancement office',
            'advancement department',
            'advancement crm',
            'advancement platform',
            'advancement system',
        ]);

        $hasConstituent = $this->hasAnyMatch($text, [
            'constituent relationship management',
            'constituent management',
            'constituent engagement',
            'constituent crm',
            'constituent database',
            'constituent platform',
        ]);

        $hasRelationshipSystem = $this->hasAnyMatch($text, [
            'crm',
            'database',
            'platform',
            'relationship management',
            'engagement',
            'constituent management',
        ]);

        $hasFundraising = $this->hasAnyMatch($text, [
            'fundraising',
            'fund raising',
            'development campaign',
            'annual giving',
            'major gifts',
            'planned giving',
            'donor management',
            'donor engagement',
        ]);

        if (
            ($hasAdvancement || $hasConstituent)
            && $hasRelationshipSystem
            && $this->hasInstitutionalContext($text)
        ) {
            return true;
        }

        if (
            $hasFundraising
            && $hasRelationshipSystem
            && $this->hasInstitutionalContext($text)
            && $this->hasAnyMatch($text, [
                'donor',
                'constituent',
                'advancement',
                'giving',
                'development',
            ])
        ) {
            return true;
        }

        return false;
    }

    private function hasInstitutionalContext(string $text): bool
    {
        return $this->hasAnyMatch($text, [
            'university',
            'universities',
            'college',
            'colleges',
            'higher education',
            'academic institution',
            'educational institution',
        ]);
    }

    private function normalize(?string $text): string
    {
        if (! $text) {
            return '';
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/i', ' ', $text) ?? '';

        return trim(
            preg_replace('/\s+/', ' ', $text) ?? ''
        );
    }

    private function hasAnyMatch(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            $term = $this->normalize($term);

            if ($term !== '' && str_contains($text, $term)) {
                return true;
            }
        }

        return false;
    }
}
