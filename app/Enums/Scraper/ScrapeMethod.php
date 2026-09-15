<?php

namespace App\Enums\Scraper;

enum ScrapeMethod: string
{
    case API = 'api';
    case UI = 'ui';
    case AI = 'ai';
    case HYBRID = 'hybrid';
}
