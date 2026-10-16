<?php

use App\Services\Rfp\AlumniRfpMatcher;

return [
    /*
    |--------------------------------------------------------------------------
    | Default RFP Filtering Keywords
    |--------------------------------------------------------------------------
    |
    | The default list of alumni and procurement keywords used by the RFPs
    | controller and frontend filter system when no custom filters are set.
    |
    */
    'default_keywords' => AlumniRfpMatcher::DEFAULT_KEYWORDS,
];
