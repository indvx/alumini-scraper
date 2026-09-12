<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class OverpassService
{
    protected array $endpoints = [
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass-api.de/api/interpreter',
    ];

    public function fetchInstitutionsByArea(int $areaId, ?string $state = null, ?string $country = null): array
    {
        $overpassQuery = <<<OVERPASS
            [out:json][timeout:180];
            (
                nwr["amenity"="school"](area:{$areaId});
                nwr["amenity"="college"](area:{$areaId});
                nwr["amenity"="university"](area:{$areaId});
                nwr["amenity"="kindergarten"](area:{$areaId});
            );
            out center tags;
            OVERPASS;

        $lastError = null;
        foreach ($this->endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'my-school-finder-laravel/1.0',
                ])->timeout(180)->asForm()->post($endpoint, [
                    'data' => $overpassQuery,
                ]);

                if ($response->successful()) {
                    return $this->parseElements($response->json('elements') ?? [], $state, $country);
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        throw new Exception("All Overpass endpoints failed. Last error: {$lastError}");
    }

    public function parseElements(array $elements, ?string $state = null, ?string $country = null): array
    {
        $records = [];
        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $type = $el['type'] ?? 'node';
            $osmId = isset($el['id']) ? (string) $el['id'] : null;

            $lat = $el['lat'] ?? $el['center']['lat'] ?? null;
            $lon = $el['lon'] ?? $el['center']['lon'] ?? null;

            $elementState = $tags['addr:state'] ?? $tags['is_in:state'] ?? $state;
            $elementCountry = $tags['addr:country'] ?? $tags['is_in:country'] ?? $tags['country'] ?? $country;

            $addressParts = array_filter([
                $tags['addr:housenumber'] ?? null,
                $tags['addr:street'] ?? null,
                $tags['addr:suburb'] ?? null,
                $tags['addr:city'] ?? null,
                $elementState,
                $elementCountry,
                $tags['addr:postcode'] ?? null,
            ]);

            $address = ! empty($addressParts) ? implode(', ', $addressParts) : null;
            $institutionType = $tags['amenity'] ?? $tags['building'] ?? $tags['landuse'] ?? 'school';

            $records[] = [
                'osm_id' => $osmId,
                'osm_type' => $type,
                'name' => $tags['name'] ?? 'Unnamed',
                'type' => strtolower((string) $institutionType),
                'latitude' => $lat !== null ? (float) $lat : null,
                'longitude' => $lon !== null ? (float) $lon : null,
                'address' => $address,
                'city' => $tags['addr:city'] ?? null,
                'state' => $elementState,
                'country' => $elementCountry,
                'postcode' => $tags['addr:postcode'] ?? null,
                'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
            ];
        }

        return $records;
    }
}
