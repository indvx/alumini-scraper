<?php

namespace App\Services\Rfp\Platforms\Bonfire;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Enums\Rfp\RfpStatus;
use App\Services\Rfp\Contracts\RfpNormalizer;

class BonfireNormalizer implements RfpNormalizer
{
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, string $source = 'api'): array
    {
        $projects = $this->extractProjects($rawPayload);
        $portalUrl = rtrim((string) $scrapeData->portalUrl, '/');
        $defaultOpportunityUrl = $portalUrl.'/portal/?tab='.($scrapeData->type === RfpStatus::PAST ? 'pastOpportunities' : 'openOpportunities');
        $rfps = [];
        foreach ($projects as $projectIdKey => $item) {
            if (! is_array($item)) {
                continue;
            }

            $projectId = (string) ($item['ProjectID'] ?? $item['project_id'] ?? $item['projectId'] ?? $item['id'] ?? (is_string($projectIdKey) && ! is_numeric($projectIdKey) ? $projectIdKey : 'PROJ-'.strtoupper(substr(md5(json_encode($item)), 0, 8))));
            $title = trim((string) ($item['ProjectName'] ?? $item['title'] ?? $item['name'] ?? $item['projectName'] ?? ''));
            if ($this->isInvalidTitle($title)) {
                continue;
            }

            $closeDateStr = $item['DateClose'] ?? $item['date_close'] ?? $item['dateClose'] ?? $item['closing_date'] ?? null;
            $dateClose = $this->normalizeDate($closeDateStr);
            if (! empty($closeDateStr) && $dateClose === null) {
                continue;
            }

            if (! $this->isWithinDateRange($dateClose, $scrapeData->fromDate, $scrapeData->toDate)) {
                continue;
            }

            $privateProjectId = $item['PrivateProjectID'] ?? $item['private_project_id'] ?? $item['privateProjectId'] ?? null;
            $referenceId = $item['ReferenceID'] ?? $item['reference_id'] ?? $item['referenceId'] ?? null;
            $department = $item['DepartmentID'] ?? $item['department'] ?? $item['department_id'] ?? null;
            $description = $item['Description'] ?? $item['description'] ?? null;
            $dateOpenStr = $item['DateOpen'] ?? $item['date_open'] ?? $item['dateOpen'] ?? null;
            $dateOpen = $this->normalizeDate($dateOpenStr);
            $isPublicAward = (bool) ($item['IsPublicAward'] ?? $item['is_public_award'] ?? $item['isPublicAward'] ?? false);
            $opportunityUrl = $this->buildOpportunityUrl($item, $portalUrl, $defaultOpportunityUrl);

            $rfps[] = new RfpData(
                projectId: $projectId,
                title: $title,
                privateProjectId: $privateProjectId ? (string) $privateProjectId : null,
                referenceId: $referenceId ? (string) $referenceId : null,
                description: $description ? (string) $description : null,
                department: $department ? (string) $department : null,
                dateOpen: $dateOpen,
                dateClose: $dateClose,
                isPublicAward: $isPublicAward,
                status: $scrapeData->type,
                source: $source,
                portalUrl: $portalUrl,
                opportunityUrl: $opportunityUrl,
                institutionId: $scrapeData->institutionId,
                rfpsPlatformId: $scrapeData->platformId,
                rawData: $item
            );
        }

        return $rfps;
    }

    protected function extractProjects(array $rawPayload): array
    {
        if (isset($rawPayload['payload']['projects']) && is_array($rawPayload['payload']['projects'])) {
            return $rawPayload['payload']['projects'];
        }
        if (isset($rawPayload['projects']) && is_array($rawPayload['projects'])) {
            return $rawPayload['projects'];
        }
        if (isset($rawPayload['items']) && is_array($rawPayload['items'])) {
            return $rawPayload['items'];
        }
        if (isset($rawPayload['data']) && is_array($rawPayload['data'])) {
            return $rawPayload['data'];
        }
        if (isset($rawPayload['rfps']) && is_array($rawPayload['rfps'])) {
            return $rawPayload['rfps'];
        }
        if (array_is_list($rawPayload)) {
            return $rawPayload;
        }

        return [];
    }

    protected function buildOpportunityUrl(array $item, string $portalUrl, string $defaultOpportunityUrl): string
    {
        $existingUrl = $item['OpportunityURL'] ?? $item['opportunity_url'] ?? $item['opportunityUrl'] ?? $item['url'] ?? null;
        if (! empty($existingUrl)) {
            $url = (string) $existingUrl;
            if (str_starts_with($url, '/')) {
                return $portalUrl.$url;
            }

            return $url;
        }
        $projectId = $item['ProjectID'] ?? $item['project_id'] ?? $item['projectId'] ?? $item['id'] ?? null;
        $visibilityId = $item['ProjectVisibilityID'] ?? $item['project_visibility_id'] ?? $item['projectVisibilityId'] ?? null;
        $privateProjectId = $item['PrivateProjectID'] ?? $item['private_project_id'] ?? $item['privateProjectId'] ?? null;
        if ($privateProjectId && (int) $visibilityId === 2) {
            return $portalUrl.'/opportunities/private/'.$privateProjectId;
        }
        if ($projectId) {
            return $portalUrl.'/opportunities/'.$projectId;
        }

        return $defaultOpportunityUrl;
    }

    protected function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        $date = trim($date);
        if ($date === '0000-00-00' || $date === '0000-00-00 00:00:00' || $date === 'null' || $date === 'NULL' || $date === '>' || $date === '">') {
            return null;
        }
        $timestamp = strtotime($date);
        if ($timestamp === false || $timestamp <= 0) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    protected function isWithinDateRange(?string $closeDate, ?string $fromDate, ?string $toDate): bool
    {
        if (empty($fromDate) && empty($toDate)) {
            return true;
        }
        if (empty($closeDate)) {
            return true;
        }
        $closeTime = strtotime($closeDate);
        if ($closeTime === false) {
            return false;
        }
        if (! empty($fromDate)) {
            $fromTime = strtotime($fromDate);
            if ($fromTime !== false && $closeTime < $fromTime) {
                return false;
            }
        }
        if (! empty($toDate)) {
            $toTime = strtotime($toDate.' 23:59:59');
            if ($toTime !== false && $closeTime > $toTime) {
                return false;
            }
        }

        return true;
    }

    protected function isInvalidTitle(string $title): bool
    {
        if ($title === '') {
            return true;
        }
        $invalidTitles = ['>', '">', '&gt;', 'View Auction'];

        return in_array($title, $invalidTitles, true);
    }
}
