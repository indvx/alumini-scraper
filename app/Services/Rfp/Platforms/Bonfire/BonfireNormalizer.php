<?php

namespace App\Services\Rfp\Platforms\Bonfire;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpNormalizer;
use Illuminate\Support\Facades\Log;

class BonfireNormalizer implements RfpNormalizer
{
    /** 
     * @param array<string, mixed> $rawPayload 
     * @return array<int, RfpData> 
     */
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, ScrapeMethod $source): array
    {
        $projects = $rawPayload['payload']['projects'] ?? $rawPayload['projects'] ?? [];
        $portalUrl = rtrim((string) $scrapeData->portalUrl, '/');
        $rfps = [];
        $defaultOpportunityUrl = $portalUrl . '/portal/?tab=' . ($scrapeData->type === RfpStatus::PAST ? 'pastOpportunities' : 'openOpportunities');
        foreach ($projects as $projectId => $item) {
            $closeDateStr = $item['DateClose'] ?? null;
            if (! $this->isWithinDateRange($closeDateStr, $scrapeData->fromDate, $scrapeData->toDate)) {
                continue;
            }
            Log::info("BonfireNormalizer: Item: ", $item);
            $opportunityUrl = $this->buildOpportunityUrl($item, $portalUrl, $defaultOpportunityUrl);

            $rfps[] = new RfpData(
                projectId: (string) ($item['ProjectID'] ?? $projectId),
                title: (string) ($item['ProjectName'] ?? 'Untitled RFP'),
                privateProjectId: $item['PrivateProjectID'] ?? null,
                referenceId: $item['ReferenceID'] ?? null,
                department: $item['DepartmentID'] ?? null,
                dateClose: $closeDateStr,
                isPublicAward: (bool) ($item['IsPublicAward'] ?? false),
                status: $this->getBonfireStatus($item) ?? $scrapeData->type,
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

    protected function getBonfireStatus(array $item): ?RfpStatus
    {
        $statusId = $item['ProjectStatusID'] ?? $item['project_status_id'] ?? null;
        $subStatusId = $item['ProjectSubStatusID'] ?? $item['project_sub_status_id'] ?? null;
        $key = "{$statusId}-{$subStatusId}";
        $map = [
            '2-1' => RfpStatus::OPEN,

            '4-1' => RfpStatus::CLOSED,
            '4-2' => RfpStatus::CANCELLED,
            '4-3' => RfpStatus::AWARDED,

            '5-1' => RfpStatus::CLOSED,
            '5-2' => RfpStatus::CANCELLED,
            '5-3' => RfpStatus::AWARDED,
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        return RfpStatus::CLOSED;
    }

    protected function isWithinDateRange(?string $closeDateStr, ?string $fromDate, ?string $toDate): bool
    {
        if (empty($fromDate) && empty($toDate)) {
            return true;
        }
        if (empty($closeDateStr)) {
            return true;
        }
        $closeTime = strtotime($closeDateStr);
        if ($closeTime === false) {
            return true;
        }
        if (! empty($fromDate)) {
            $fromTime = strtotime($fromDate);
            if ($fromTime !== false && $closeTime < $fromTime) {
                return false;
            }
        }
        if (! empty($toDate)) {
            $toTime = strtotime($toDate . ' 23:59:59');
            if ($toTime !== false && $closeTime > $toTime) {
                return false;
            }
        }
        return true;
    }
    protected function buildOpportunityUrl(array $item, string $portalUrl, string $defaultOpportunityUrl): string
    {
        $existingUrl = $item['OpportunityURL'] ?? $item['opportunity_url'] ?? $item['opportunityUrl'] ?? $item['url'] ?? null;
        if (! empty($existingUrl)) {
            $url = (string) $existingUrl;
            if (str_starts_with($url, '/')) {
                return $portalUrl . $url;
            }

            return $url;
        }
        $projectId = $item['ProjectID'] ?? $item['project_id'] ?? $item['projectId'] ?? $item['id'] ?? null;
        $visibilityId = $item['ProjectVisibilityID'] ?? $item['project_visibility_id'] ?? $item['projectVisibilityId'] ?? null;
        $privateProjectId = $item['PrivateProjectID'] ?? $item['private_project_id'] ?? $item['privateProjectId'] ?? null;
        if ($privateProjectId && (int) $visibilityId === 2) {
            return $portalUrl . '/opportunities/private/' . $privateProjectId;
        }
        if ($projectId) {
            return $portalUrl . '/opportunities/' . $projectId;
        }

        return $defaultOpportunityUrl;
    }
}
