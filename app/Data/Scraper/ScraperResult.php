<?php

namespace App\Data\Scraper;

use App\Data\Rfp\RfpData;
use App\Enums\Scraper\ScrapeMethod;
use App\Enums\Scraper\ScrapeStatus;

class ScraperResult
{
    /**
     * @param  array<int, RfpData>  $rfps
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ScrapeStatus $status,
        public array $rfps = [],
        public ?string $errorMessage = null,
        public ScrapeMethod $method = ScrapeMethod::API,
        public array $metadata = []
    ) {}

    public static function success(array $rfps, ScrapeMethod $method = ScrapeMethod::API, array $metadata = []): self
    {
        return new self(
            status: ScrapeStatus::SUCCESS,
            rfps: $rfps,
            method: $method,
            metadata: $metadata
        );
    }

    public static function failure(string $errorMessage, ScrapeMethod $method = ScrapeMethod::API, array $metadata = []): self
    {
        return new self(
            status: ScrapeStatus::FAILED,
            rfps: [],
            errorMessage: $errorMessage,
            method: $method,
            metadata: $metadata
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === ScrapeStatus::SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this->status === ScrapeStatus::FAILED;
    }

    public function count(): int
    {
        return count($this->rfps);
    }
}
