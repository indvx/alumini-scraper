<?php

namespace App\Services\Rfp;

use App\Data\Rfp\RfpData;

final class AlumniRfpMatcher
{
    public function isRelevant(RfpData $rfp): bool
    {
        $text = strtolower(
            implode(' ', array_filter([
                $rfp->title,
                $rfp->description,
                $rfp->department,
            ]))
        );

        $score = 0;

        // Strong direct signals
        $score += $this->countMatches($text, [
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
        ]) * 100;

        // Alumni-specific activities
        $score += $this->countMatches($text, [
            'alumni database',
            'alumni crm',
            'alumni management',
            'alumni portal',
            'alumni platform',
            'alumni directory',
            'alumni fundraising',
            'alumni giving',
            'alumni event',
            'alumni events',
            'alumni reunion',
        ]) * 80;

        // Indirect signals
        $score += $this->countMatches($text, [
            'constituent engagement',
            'constituent management',
            'constituent relationship',
            'constituent crm',
            'advancement crm',
            'advancement platform',
            'institutional advancement',
            'donor engagement',
            'donor management',
            'fundraising platform',
        ]) * 20;

        // Indirect keywords alone should NOT be enough. They need some alumni-related context.
        $hasDirectAlumniContext = $this->hasAnyMatch($text, [
            'alumni',
            'alumnus',
            'alumna',
            'alumnae',
            'former student',
            'former students',
            'graduate relations',
        ]);

        if ($hasDirectAlumniContext) {
            return $score >= 100;
        }

        return $this->hasAlumniContextCombination($text);
    }

    private function hasAlumniContextCombination(string $text): bool
    {
        $contextTerms = [
            'advancement',
            'constituent',
            'donor',
            'fundraising',
            'giving',
            'development',
        ];

        $engagementTerms = [
            'crm',
            'engagement',
            'relationship management',
            'database',
            'platform',
            'management system',
        ];

        return $this->hasAnyMatch($text, $contextTerms)
            && $this->hasAnyMatch($text, $engagementTerms)
            && $this->hasInstitutionalContext($text);
    }

    private function hasInstitutionalContext(string $text): bool
    {
        return $this->hasAnyMatch($text, [
            'university',
            'college',
            'higher education',
            'institution',
            'graduates',
            'students',
        ]);
    }

    private function hasAnyMatch(string $text, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($text, strtolower($term))) {
                return true;
            }
        }

        return false;
    }

    private function countMatches(string $text, array $terms): int
    {
        $count = 0;

        foreach ($terms as $term) {
            if (str_contains($text, strtolower($term))) {
                $count++;
            }
        }

        return $count;
    }
}
