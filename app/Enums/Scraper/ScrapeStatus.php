<?php

namespace App\Enums\Scraper;

enum ScrapeStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SUCCESS = 'success';
    case PARTIAL = 'partial';
    case FAILED = 'failed';
}
