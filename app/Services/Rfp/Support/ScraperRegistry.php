<?php

namespace App\Services\Rfp\Support;


use Illuminate\Support\Facades\Log;
use App\Services\Rfp\Contracts\RfpPlatformScraper;
use App\Services\Rfp\Platforms\BidNetDirect\BidNetDirectScraper;
use App\Services\Rfp\Platforms\Bonfire\BonfireScraper;
use App\Services\Rfp\Platforms\OpenGov\OpenGovScraper;
use Illuminate\Support\ItemNotFoundException;

class ScraperRegistry
{
    /**
     * @var array<int, RfpPlatformScraper>
     */
    protected array $scrapers;

    public function __construct(
        BonfireScraper $bonfireScraper,
        OpenGovScraper $openGovScraper,
        BidNetDirectScraper $bidNetDirectScraper
    ) {
        $this->scrapers = [
            $bonfireScraper,
            $openGovScraper,
            $bidNetDirectScraper,
        ];
    }

    public function getScraperForPlatform(string $platform): RfpPlatformScraper
    {
        foreach ($this->scrapers as $scraper) {
            if ($scraper->supports($platform)) {
                Log::debug('RFP scraper registry matched platform.', ['requested_platform' => $platform, 'scraper_class' => get_class($scraper), 'resolved_platform' => $scraper->getPlatformName()]);
                return $scraper;
            }
        }

        Log::error('No RFP scraper registered for requested platform.', ['requested_platform' => $platform, 'registered_scrapers' => array_map(fn ($scraper) => $scraper->getPlatformName(), $this->scrapers)]);
        throw new ItemNotFoundException("Scraper for platform '{$platform}' not found.");
    }

    public function registerScraper(RfpPlatformScraper $scraper): void
    {
        $this->scrapers[] = $scraper;
    }
}
