<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class NominatimService
{
    protected string $baseUrl = 'https://nominatim.openstreetmap.org';

    public function resolveLocationArea(string $query): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'my-school-finder-laravel/1.0',
        ])->timeout(30)->get($this->baseUrl.'/search', [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'accept-language' => 'en',
            'limit' => 10,
        ]);

        if ($response->failed()) {
            throw new Exception('Nominatim geocoding failed: '.$response->body());
        }

        $data = $response->json();
        if (empty($data)) {
            throw new Exception("Location not found: {$query}");
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

    public function findInstitutionName(float $latitude, float $longitude): ?string
    {
        try {
            $params = [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'zoom' => 18,
                'addressdetails' => 1,
            ];

            $response = Http::withHeaders([
                'User-Agent' => 'SchoolNameFinder/1.0',
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
