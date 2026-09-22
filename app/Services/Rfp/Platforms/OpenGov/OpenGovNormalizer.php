<?php

namespace App\Services\Rfp\Platforms\OpenGov;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpNormalizer;

class OpenGovNormalizer implements RfpNormalizer
{
    /**
     * @param  array<string, mixed>  $rawPayload
     * @return array<int, RfpData>
     */
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, ScrapeMethod $source): array
    {
        $projects = [];
        if (isset($rawPayload['id']) || isset($rawPayload['title'])) {
            $projects = [$rawPayload];
        } elseif (isset($rawPayload['rows']) && is_array($rawPayload['rows'])) {
            $projects = $rawPayload['rows'];
        } elseif (isset($rawPayload['projects']) && is_array($rawPayload['projects'])) {
            $projects = $rawPayload['projects'];
        } elseif (isset($rawPayload['data']) && is_array($rawPayload['data'])) {
            $projects = $rawPayload['data'];
        } elseif (isset($rawPayload['results']) && is_array($rawPayload['results'])) {
            $projects = $rawPayload['results'];
        } elseif (array_is_list($rawPayload)) {
            $projects = $rawPayload;
        }

        $portalUrl = rtrim((string) $scrapeData->portalUrl, '/');
        $govCode = OpenGovConfig::extractGovCode($portalUrl) ?? $scrapeData->options['gov_code'] ?? 'surs';

        $rfps = [];
        foreach ($projects as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $closeDateStr = $item['proposalDeadline'] ?? $item['date_close'] ?? null;
            if (! $this->isWithinDateRange($closeDateStr, $scrapeData->fromDate, $scrapeData->toDate)) {
                continue;
            }

            $itemGovCode = $item['government']['code'] ?? $govCode;
            $projectId = (string) ($item['id'] ?? $item['financialId'] ?? $key);
            $opportunityUrl = $this->buildOpportunityUrl($item, $portalUrl, $itemGovCode, $projectId);

            $statusStr = strtolower((string) ($item['status'] ?? ''));
            $closedSubstatus = strtolower((string) ($item['closedSubstatus'] ?? ''));

            $statusEnum = match (true) {
                $statusStr === 'open' => RfpStatus::OPEN,
                $closedSubstatus === 'awarded' || $statusStr === 'awarded' => RfpStatus::AWARDED,
                $closedSubstatus === 'canceled' || $closedSubstatus === 'cancelled' || $statusStr === 'cancelled' || $statusStr === 'canceled' => RfpStatus::CANCELLED,
                $statusStr === 'closed' || $statusStr === 'past' || $statusStr === 'evaluation' => RfpStatus::PAST,
                default => $scrapeData->type,
            };

            $isPublicAward = $closedSubstatus === 'awarded' || $statusStr === 'awarded' || (bool) ($item['isPublicAward'] ?? false);

            $department = null;
            if (isset($item['department']['name'])) {
                $department = (string) $item['department']['name'];
            } elseif (is_string($item['department'] ?? null)) {
                $department = $item['department'];
            }

            $rfps[] = new RfpData(
                projectId: $projectId,
                title: (string) ($item['title'] ?? $item['ProjectName'] ?? 'Untitled RFP'),
                privateProjectId: ! empty($item['isPrivate']) ? (string) $projectId : null,
                referenceId: isset($item['financialId']) ? (string) $item['financialId'] : null,
                description: isset($item['summary']) ? (string) $item['summary'] : null,
                department: $department,
                dateOpen: $item['releaseProjectDate'] ?? $item['created_at'] ?? null,
                dateClose: $closeDateStr,
                isPublicAward: $isPublicAward,
                status: $statusEnum,
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

    protected function buildOpportunityUrl(array $item, string $portalUrl, string $govCode, string $projectId): string
    {
        $existingUrl = $item['opportunity_url'] ?? $item['opportunityUrl'] ?? $item['url'] ?? null;
        if (! empty($existingUrl)) {
            $url = (string) $existingUrl;
            if (str_starts_with($url, '/')) {
                return 'https://procurement.opengov.com' . $url;
            }

            return $url;
        }

        if (! empty($govCode) && ! empty($projectId)) {
            return OpenGovConfig::buildOpportunityUrl($govCode, $projectId);
        }

        return $portalUrl;
    }
}
