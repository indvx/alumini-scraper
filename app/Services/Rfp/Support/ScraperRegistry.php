<?php

namespace App\Services\Rfp\Support;

use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\Bonfire\BonfireScraper;
use Illuminate\Support\ItemNotFoundException;

class ScraperRegistry
{
    /**
     * @var array<int, RfpPlatformScraper>
     */
    protected array $scrapers;

    public function __construct(
        BonfireScraper $bonfireScraper
    ) {
        $this->scrapers = [
            $bonfireScraper,
        ];
    }

    public function getScraperForPlatform(string $platform): RfpPlatformScraper
    {
        foreach ($this->scrapers as $scraper) {
            if ($scraper->supports($platform)) {
                return $scraper;
            }
        }

        throw new ItemNotFoundException("Scraper for platform '{$platform}' not found.");
    }

    public function registerScraper(RfpPlatformScraper $scraper): void
    {
        $this->scrapers[] = $scraper;
    }
}
