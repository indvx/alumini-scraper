<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NominatimService
{
    protected string $baseUrl;

    protected string $userAgent;

    public function __construct()
    {
        $this->baseUrl = config('services.nominatim.base_url', env('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'));
        $this->userAgent = config('services.nominatim.user_agent', env('NOMINATIM_USER_AGENT', 'Mozilla/5.0 (compatible; AlumniInstitutionFinder/1.0)'));
    }

    public function resolveLocationArea(string $query): array
    {
        $cacheKey = 'nominatim_area_'.md5(strtolower(trim($query)));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query) {
            try {
                return $this->fetchLocationAreaFromNominatim($query);
            } catch (Exception $e) {
                Log::warning('Nominatim geocoding failed: '.$e->getMessage().'. Attempting Photon fallback.');

                return $this->fetchLocationAreaFromPhoton($query);
            }
        });
    }

    protected function fetchLocationAreaFromNominatim(string $query): array
    {
        usleep(1000000); // Respect 1 req/sec policy

        $response = Http::retry(2, 2000, function (Exception $exception, $request) {
            if (method_exists($exception, 'getResponse') && $exception->getResponse()) {
                return $exception->getResponse()->status() === 429;
            }

            return false;
        }, throw: false)->withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(30)->get($this->baseUrl.'/search', [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'accept-language' => 'en',
            'limit' => 10,
        ]);

        if ($response->status() === 403) {
            throw new Exception('Nominatim HTTP 403 (Forbidden). Check User-Agent header or API limits.');
        }

        if ($response->status() === 429) {
            throw new Exception('Nominatim HTTP 429 (Rate Limit Exceeded).');
        }

        if ($response->failed()) {
            throw new Exception('Nominatim failed with status HTTP '.$response->status());
        }

        $data = $response->json();
        if (empty($data) || ! is_array($data)) {
            throw new Exception("Location not found via Nominatim: {$query}");
        }

        $requestedName = strtolower(trim(explode(',', $query)[0]));
        $place = null;

        foreach ($data as $item) {
            if (($item['osm_type'] ?? '') === 'relation' && strtolower(trim($item['name'] ?? '')) === $requestedName) {
                $place = $item;
                break;
            }
        }

        if (! $place) {
            $place = collect($data)->firstWhere('osm_type', 'relation');
        }

        if (! $place) {
            $place = $data[0] ?? null;
        }

        if (! $place) {
            throw new Exception("No administrative boundary found for '{$query}'");
        }

        $osmType = $place['osm_type'] ?? 'relation';
        $osmId = (int) ($place['osm_id'] ?? 0);

        $areaId = match ($osmType) {
            'relation' => 3600000000 + $osmId,
            'way' => 2400000000 + $osmId,
            default => 3600000000 + $osmId,
        };

        $address = $place['address'] ?? [];
        $state = $address['state'] ?? $address['province'] ?? $address['region'] ?? null;
        $country = $address['country'] ?? null;

        return [
            'display_name' => $place['display_name'] ?? $query,
            'area_id' => $areaId,
            'place_name' => $place['name'] ?? $query,
            'state' => $state,
            'country' => $country,
        ];
    }

    protected function fetchLocationAreaFromPhoton(string $query): array
    {
        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent,
        ])->timeout(30)->get('https://photon.komoot.io/api/', [
            'q' => $query,
            'limit' => 5,
            'lang' => 'en',
        ]);

        if ($response->failed()) {
            throw new Exception("Geocoding failed for '{$query}'. Both Nominatim and Photon services were unreachable.");
        }

        $features = $response->json('features');
        if (empty($features) || ! is_array($features)) {
            throw new Exception("Location not found: {$query}");
        }

        $bestFeature = $features[0];
        $properties = $bestFeature['properties'] ?? [];

        $osmTypeRaw = strtoupper((string) ($properties['osm_type'] ?? 'R'));
        $osmId = (int) ($properties['osm_id'] ?? 0);

        $areaId = match ($osmTypeRaw) {
            'R', 'RELATION' => 3600000000 + $osmId,
            'W', 'WAY' => 2400000000 + $osmId,
            default => 3600000000 + $osmId,
        };

        $placeName = $properties['name'] ?? $query;
        $state = $properties['state'] ?? null;
        $country = $properties['country'] ?? null;

        $displayNameParts = array_filter([$placeName, $state, $country]);
        $displayName = ! empty($displayNameParts) ? implode(', ', $displayNameParts) : $query;

        return [
            'display_name' => $displayName,
            'area_id' => $areaId,
            'place_name' => $placeName,
            'state' => $state,
            'country' => $country,
        ];
    }

    public function findInstitutionName(float $latitude, float $longitude): ?string
    {
        try {
            usleep(1000000);

            $params = [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'zoom' => 18,
                'addressdetails' => 1,
            ];

            $response = Http::retry(2, 2000, throw: false)->withHeaders([
                'User-Agent' => $this->userAgent,
            ])->timeout(30)->get($this->baseUrl.'/reverse', $params);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            $name = $data['name'] ?? null;
            $unnamedValues = [
                'unnamed',
                'unnamed institution',
                'unnamed school',
                'unnamed college',
                'unnamed university',
                'unnamed kindergarten',
            ];

            if (! empty($name) && ! in_array(strtolower(trim($name)), $unnamedValues, true)) {
                return $name;
            }

            $address = $data['address'] ?? [];
            $addressKeys = ['school', 'university', 'college', 'kindergarten', 'amenity', 'building', 'name'];
            foreach ($addressKeys as $key) {
                if (! empty($address[$key]) && ! in_array(strtolower(trim($address[$key])), $unnamedValues, true)) {
                    return $address[$key];
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
