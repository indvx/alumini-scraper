<?php

namespace App\Enums\Rfp;

enum RfpStatus: string
{
    case OPEN = 'open';
    case PAST = 'past';
    case CLOSED = 'closed';
    case AWARDED = 'awarded';
    case CANCELLED = 'cancelled';
    case UNKNOWN = 'unknown';
    case ALL = 'all';
}
