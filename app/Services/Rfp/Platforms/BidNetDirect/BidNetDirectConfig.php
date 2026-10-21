<?php

namespace App\Services\Rfp\Platforms\BidNetDirect;

class BidNetDirectConfig
{
    public const ROOT_URL = 'https://www.bidnetdirect.com';

    public const BASE_URL = 'https://www.bidnetdirect.com/solicitations';

    public const CONTENT_GROUP_MEMBER_AGENCY = '2085061601';

    public const CONTENT_GROUP_STATE_LOCAL = '2085061651';

    public const CONTENT_GROUP_FEDERAL = '2125256051';

    public const CATEGORY_EDUCATIONAL_SERVICES = '321798';

    public const LOCATION_FILTERS = [
        'Alabama' => '19',
        'Alaska' => '25',
        'American Samoa' => '24000',
        'Arizona' => '31',
        'Arkansas' => '37',
        'California' => '43',
        'Colorado' => '49',
        'Connecticut' => '55',
        'Delaware' => '61',
        'District of Columbia' => '67',
        'Florida' => '73',
        'Georgia' => '79',
        'Guam' => '40952',
        'Hawaii' => '85',
        'Idaho' => '91',
        'Illinois' => '97',
        'Indiana' => '103',
        'Iowa' => '109',
        'Kansas' => '115',
        'Kentucky' => '121',
        'Louisiana' => '127',
        'Maine' => '133',
        'Maryland' => '139',
        'Massachusetts' => '145',
        'Michigan' => '151',
        'Minnesota' => '157',
        'Mississippi' => '163',
        'Missouri' => '169',
        'Montana' => '175',
        'Nebraska' => '181',
        'Nevada' => '187',
        'New Hampshire' => '193',
        'New Jersey' => '199',
        'New Mexico' => '205',
        'New York' => '211',
        'North Carolina' => '217',
        'North Dakota' => '223',
        'Northern Mariana Islands' => '24006',
        'Not Stated' => '110000',
        'Ohio' => '229',
        'Oklahoma' => '235',
        'Oregon' => '241',
        'Pennsylvania' => '247',
        'Puerto Rico' => '20178',
        'Rhode Island' => '253',
        'South Carolina' => '259',
        'South Dakota' => '265',
        'Tennessee' => '271',
        'Texas' => '277',
        'US Virgin Islands' => '24011',
        'Utah' => '283',
        'Vermont' => '289',
        'Virginia' => '295',
        'Washington' => '301',
        'West Virginia' => '307',
        'Wisconsin' => '313',
        'Wyoming' => '319',
    ];

    public const STATE_CODE_TO_NAME = [
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AS' => 'American Samoa',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'GU' => 'Guam',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'MP' => 'Northern Mariana Islands',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'PR' => 'Puerto Rico',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'VI' => 'US Virgin Islands',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
    ];

    /**
     * Resolve state name or postal code to BidNet Direct location code.
     */
    public static function getLocationCode(?string $stateNameOrCode): ?string
    {
        if (empty($stateNameOrCode)) {
            return null;
        }

        $trimmed = trim($stateNameOrCode);
        $upper = strtoupper($trimmed);

        if (isset(self::STATE_CODE_TO_NAME[$upper])) {
            $stateName = self::STATE_CODE_TO_NAME[$upper];

            return self::LOCATION_FILTERS[$stateName] ?? null;
        }

        $standardized = ucwords(strtolower($trimmed));

        return self::LOCATION_FILTERS[$standardized] ?? null;
    }

    /**
     * Get public search portal URL based on RFP status type.
     */
    public static function getPublicPortalUrl(string $type = 'open'): string
    {
        return match (strtolower($type)) {
            'past', 'closed' => self::BASE_URL.'/closed-bids',
            default => self::BASE_URL.'/open-bids',
        };
    }

    /**
     * Build absolute opportunity URL from path.
     */
    public static function buildOpportunityUrl(string $path, ?string $baseUrl = null): string
    {
        return self::absoluteUrl($path, $baseUrl);
    }

    /**
     * Convert relative links to absolute URLs.
     */
    public static function absoluteUrl(string $url, ?string $baseUrl = null): string
    {
        $url = trim($url);

        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        $base = $baseUrl ?: self::ROOT_URL;
        $parts = parse_url($base);

        $origin = ($parts['scheme'] ?? 'https')
            .'://'
            .($parts['host'] ?? 'www.bidnetdirect.com');

        if (str_starts_with($url, '//')) {
            return ($parts['scheme'] ?? 'https').':'.$url;
        }

        if (str_starts_with($url, '/')) {
            return $origin.$url;
        }

        $path = $parts['path'] ?? '/';
        $directory = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $origin.($directory ? $directory.'/' : '/').$url;
    }

    /**
     * Construct the URL for a paginated listing page.
     * Subsequent pages use the /page{N} URL pattern.
     */
    public static function buildListingUrl(string $baseUrl, int $page): string
    {
        if ($page <= 1) {
            return $baseUrl;
        }

        $parts = parse_url($baseUrl);
        $path = rtrim($parts['path'] ?? '', '/');
        $path = (string) preg_replace('~/page\d+$~i', '', $path);
        $origin = ($parts['scheme'] ?? 'https')
            .'://'
            .($parts['host'] ?? 'www.bidnetdirect.com');

        $url = $origin.$path.'/page'.$page;

        if (! empty($parts['query'])) {
            $url .= '?'.$parts['query'];
        }

        return $url;
    }

    /**
     * Build search query URL with filters.
     *
     * @param  array<string, mixed>  $params
     */
    public static function buildSearchUrl(array $params = []): string
    {
        $type = $params['type'] ?? 'open';
        $baseUrl = self::getPublicPortalUrl($type);

        $queryParams = [];

        if (! empty($params['keywords'])) {
            $queryParams['keywords'] = (string) $params['keywords'];
        }

        if (! empty($params['location'])) {
            $locationCode = is_numeric($params['location'])
                ? (string) $params['location']
                : self::getLocationCode((string) $params['location']);

            if (! empty($locationCode)) {
                $queryParams['location'] = $locationCode;
            }
        }

        if (! empty($params['searchContentGroupId'])) {
            $queryParams['searchContentGroupId'] = (string) $params['searchContentGroupId'];
        }

        if (! empty($params['category'])) {
            $queryParams['category'] = (string) $params['category'];
        }

        if (! empty($params['publishDate'])) {
            $queryParams['publishDate'] = (string) $params['publishDate'];
        }

        if (! empty($params['page']) && (int) $params['page'] > 1) {
            $queryParams['page'] = (int) $params['page'];
        }

        if (empty($queryParams)) {
            return $baseUrl;
        }

        return $baseUrl.'?'.http_build_query($queryParams);
    }
}
