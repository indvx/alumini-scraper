<?php

namespace App\Data\Rfp;

use App\Enums\Rfp\RfpStatus;
use App\Enums\Scraper\ScrapeMethod;

class RfpData
{
    public function __construct(
        public string $projectId,
        public string $title,
        public ?string $privateProjectId = null,
        public ?string $referenceId = null,
        public ?string $description = null,
        public ?string $department = null,
        public ?string $dateOpen = null,
        public ?string $dateClose = null,
        public bool $isPublicAward = false,
        public RfpStatus $status = RfpStatus::OPEN,
        public ScrapeMethod $source = ScrapeMethod::API,
        public ?string $portalUrl = null,
        public ?string $opportunityUrl = null,
        public ?int $institutionId = null,
        public ?int $rfpsPlatformId = null,
        public array $rawData = []
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->projectId,
            'private_project_id' => $this->privateProjectId,
            'reference_id' => $this->referenceId,
            'title' => $this->title,
            'description' => $this->description,
            'department' => $this->department,
            'date_open' => $this->dateOpen,
            'date_close' => $this->dateClose,
            'is_public_award' => $this->isPublicAward,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'portal_url' => $this->portalUrl,
            'opportunity_url' => $this->opportunityUrl,
            'institution_id' => $this->institutionId,
            'rfps_platform_id' => $this->rfpsPlatformId,
            'raw_data' => $this->rawData,
        ];
    }
}
