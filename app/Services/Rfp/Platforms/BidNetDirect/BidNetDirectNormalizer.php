<?php

namespace App\Services\Rfp\Platforms\BidNetDirect;

use App\Data\Rfp\RfpData;
use App\Data\Rfp\RfpScrapeData;
use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;
use App\Services\Rfp\Contracts\RfpNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class BidNetDirectNormalizer implements RfpNormalizer
{
    /**
     * Normalize raw BidNet Direct payload into RfpData instances.
     *
     * @param  array<string, mixed>  $rawPayload
     * @return array<int, RfpData>
     */
    public function normalize(array $rawPayload, RfpScrapeData $scrapeData, ScrapeMethod $source): array
    {
        if (isset($rawPayload['html']) && is_string($rawPayload['html'])) {
            return $this->normalizeHtml($rawPayload['html'], $scrapeData, $source);
        }

        $items = [];
        if (isset($rawPayload['solicitations']) && is_array($rawPayload['solicitations'])) {
            $items = $rawPayload['solicitations'];
        } elseif (isset($rawPayload['rows']) && is_array($rawPayload['rows'])) {
            $items = $rawPayload['rows'];
        } elseif (isset($rawPayload['projects']) && is_array($rawPayload['projects'])) {
            $items = $rawPayload['projects'];
        } elseif (array_is_list($rawPayload)) {
            $items = $rawPayload;
        }

        return $this->normalizeItems($items, $scrapeData, $source);
    }

    /**
     * Parse and normalize HTML content using Symfony DomCrawler.
     *
     * @return array<int, RfpData>
     */
    public function normalizeHtml(string $html, RfpScrapeData $scrapeData, ScrapeMethod $source): array
    {
        Log::debug('BidNetDirectNormalizer::normalizeHtml', [
            'html' => $html,
            'scrapeData' => $scrapeData,
            'source' => $source,
        ]);
        if (trim($html) === '') {
            return [];
        }

        $portalUrl = rtrim((string) ($scrapeData->portalUrl ?: BidNetDirectConfig::getPublicPortalUrl($scrapeData->type->value)), '/');
        $crawler = new Crawler($html, $portalUrl);

        $links = $crawler->filter('a.solicitation-link');

        if ($links->count() === 0) {
            $rows = $crawler->filter('#solicitationsList tbody tr, table.simpleSolResultsTable tbody tr, tr.mets-table-row');
            if ($rows->count() > 0) {
                $links = $rows->filter('a.solicitation-link');
            }
        }

        Log::debug('BidNetDirectNormalizer::normalizeHtml', [
            'total links' => $links->count(),
        ]);

        if ($links->count() === 0) {
            return [];
        }

        $rfps = [];

        $links->each(function (Crawler $link) use ($scrapeData, $source, $portalUrl, &$rfps) {
            $href = $link->attr('href');
            if (! $href) {
                return;
            }

            $elementId = $link->attr('id') ?? '';
            $idNodeText = $this->firstText($link, '.accessibility-hidden') ?? '';

            $projectId = $this->resolveProjectId($idNodeText, $elementId, $href);
            if (! $projectId) {
                return;
            }

            $title = $this->firstText($link, '.rowTitle');
            if (! $title) {
                $title = $this->cleanText($link->text(' ', false));
            }
            if ($title === '') {
                return;
            }

            $solicitationNumber = $this->extractSolicitationNumber($title);
            $location = $this->firstText($link, '.location');

            $timeRemaining = $this->firstText($link, '.timeRemaining');
            $pubDateStr = $this->firstText($link, '.publicationDate .dateValue');
            $closeDateStr = $this->firstText($link, '.closingDate .dateValue');
            $statusLabel = $this->firstText($link, '.closingDate .dateLabel');

            if (! $this->isWithinDateRange($closeDateStr, $scrapeData->fromDate, $scrapeData->toDate)) {
                return;
            }

            $tooltipNode = $link->filter('[data-mets-tooltip]');
            $coverageType = $tooltipNode->count() > 0 ? trim($tooltipNode->attr('data-mets-tooltip') ?? '') : null;

            $closingDateParent = $link->filter('.closingDate');
            $statusEnum = $this->resolveStatus($closingDateParent, $closeDateStr, $statusLabel, $scrapeData->type);
            $opportunityUrl = BidNetDirectConfig::absoluteUrl($href, $portalUrl);

            $department = $coverageType ?: ($location ? "Region: {$location}" : null);

            $rawData = [
                'external_id' => $projectId,
                'project_id' => $projectId,
                'solicitation_number' => $solicitationNumber,
                'title' => $title,
                'location' => $location,
                'publication_date' => $pubDateStr,
                'published_at' => $this->parseDate($pubDateStr),
                'closing_date' => $closeDateStr,
                'closing_at' => $this->parseDate($closeDateStr),
                'time_remaining' => $timeRemaining,
                'coverage_type' => $coverageType,
                'status_label' => $statusLabel,
                'opportunity_url' => $opportunityUrl,
                'source_url' => $opportunityUrl,
                'href' => $href,
            ];

            $rfps[] = new RfpData(
                projectId: $projectId,
                title: $title,
                privateProjectId: null,
                referenceId: $solicitationNumber,
                description: null,
                department: $department,
                dateOpen: $this->parseDate($pubDateStr),
                dateClose: $this->parseDate($closeDateStr),
                isPublicAward: false,
                status: $statusEnum,
                source: $source,
                portalUrl: $portalUrl,
                opportunityUrl: $opportunityUrl,
                institutionId: $scrapeData->institutionId,
                rfpsPlatformId: $scrapeData->platformId,
                rawData: $rawData
            );
        });

        return $rfps;
    }

    /**
     * Normalize pre-parsed structured array items.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, RfpData>
     */
    public function normalizeItems(array $items, RfpScrapeData $scrapeData, ScrapeMethod $source): array
    {
        $portalUrl = rtrim((string) ($scrapeData->portalUrl ?: BidNetDirectConfig::getPublicPortalUrl($scrapeData->type->value)), '/');
        $rfps = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = (string) ($item['title'] ?? $item['name'] ?? 'Untitled RFP');
            $closeDateStr = $item['closing_date'] ?? $item['date_close'] ?? $item['closeDate'] ?? null;

            if (! $this->isWithinDateRange($closeDateStr, $scrapeData->fromDate, $scrapeData->toDate)) {
                continue;
            }

            $projectId = (string) ($item['project_id'] ?? $item['notice_id'] ?? $item['external_id'] ?? $item['id'] ?? $index);
            $solicitationNumber = $item['solicitation_number'] ?? $item['reference_id'] ?? $this->extractSolicitationNumber($title);
            $href = (string) ($item['href'] ?? $item['url'] ?? $item['opportunity_url'] ?? $item['source_url'] ?? '');
            $opportunityUrl = $href !== '' ? BidNetDirectConfig::absoluteUrl($href, $portalUrl) : $portalUrl;
            $openDateStr = $item['publication_date'] ?? $item['date_open'] ?? $item['openDate'] ?? null;
            $coverageType = $item['coverage_type'] ?? null;
            $location = $item['location'] ?? null;

            $department = $coverageType ?: ($location ? "Region: {$location}" : null);

            $rfps[] = new RfpData(
                projectId: $projectId,
                title: $title,
                privateProjectId: null,
                referenceId: $solicitationNumber,
                description: $item['description'] ?? null,
                department: $department,
                dateOpen: $this->parseDate($openDateStr),
                dateClose: $this->parseDate($closeDateStr),
                isPublicAward: (bool) ($item['is_public_award'] ?? false),
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

    /**
     * Best-effort extraction of a solicitation/reference number from title.
     */
    public function extractSolicitationNumber(?string $title): ?string
    {
        if (! $title) {
            return null;
        }

        if (preg_match('/^([A-Z0-9]+(?:-[A-Z0-9]+)*(?:\/[A-Z0-9-]+)?)/i', trim($title), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Read the first non-empty matching element text.
     */
    public function firstText(Crawler $crawler, string $selector): ?string
    {
        $elements = $crawler->filter($selector);
        if ($elements->count() === 0) {
            return null;
        }

        $text = $this->cleanText($elements->first()->text('', false));

        return $text !== '' ? $text : null;
    }

    /**
     * Normalize whitespace and remove non-breaking spaces.
     */
    public function cleanText(string $text): string
    {
        $text = preg_replace('/[\x{00A0}\s]+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Convert displayed date into standard Y-m-d format.
     */
    public function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);

        foreach (['m/d/Y', 'n/j/Y', 'Y-m-d', 'm-d-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (Throwable) {
                // Try next format
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return $value;
        }
    }

    /**
     * Extract project notice ID from element values.
     */
    protected function resolveProjectId(string $extractedId, string $linkId, string $href): ?string
    {
        if (preg_match('/searchResultSol_notice_(\d+)/', $linkId, $matches)) {
            return $matches[1];
        }

        $extractedTrimmed = trim($extractedId);
        if ($extractedTrimmed !== '' && is_numeric($extractedTrimmed)) {
            return $extractedTrimmed;
        }

        if (preg_match('~/(\d+)(?:[/?#]|$)~', $href, $matches)) {
            return $matches[1];
        }

        return $extractedTrimmed !== '' ? $extractedTrimmed : null;
    }

    /**
     * Determine RFP status from closing node classes, label, and date.
     */
    protected function resolveStatus(
        Crawler $closingDateNode,
        ?string $closeDateStr,
        ?string $statusLabel,
        RfpStatus $defaultStatus
    ): RfpStatus {
        if ($statusLabel !== null) {
            $label = strtolower($statusLabel);
            if (str_contains($label, 'closed') || str_contains($label, 'expired')) {
                return RfpStatus::CLOSED;
            }
            if (str_contains($label, 'closing') || str_contains($label, 'open')) {
                return RfpStatus::OPEN;
            }
            if (str_contains($label, 'awarded')) {
                return RfpStatus::AWARDED;
            }
        }

        if ($closingDateNode->count() > 0) {
            $class = $closingDateNode->attr('class') ?? '';
            if (str_contains($class, 'closed')) {
                return RfpStatus::CLOSED;
            }
            if (str_contains($class, 'open')) {
                return RfpStatus::OPEN;
            }
        }

        if (! empty($closeDateStr)) {
            $time = strtotime($closeDateStr);
            if ($time !== false && $time < time()) {
                return RfpStatus::CLOSED;
            }
        }

        return $defaultStatus;
    }

    /**
     * Check if closing date falls within date range filter.
     */
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
            $toTime = strtotime($toDate.' 23:59:59');
            if ($toTime !== false && $closeTime > $toTime) {
                return false;
            }
        }

        return true;
    }
}
