<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OverpassService
{
    protected array $overpassEndpoints = [
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass-api.de/api/interpreter',
    ];

    protected string $nominatimEndpoint = 'https://nominatim.openstreetmap.org/reverse';

    protected string $userAgent = 'my-school-finder-laravel/1.0';

    protected array $nominatimCache = [];

    protected int $minimumValidationScore = 5;

    public function fetchInstitutionsByArea(
        int $areaId,
        ?string $state = null,
        ?string $country = null
    ): array {
        $overpassQuery = <<<OVERPASS
            [out:json][timeout:180];
            (
                node["amenity"="university"](area:{$areaId});
                way["amenity"="university"](area:{$areaId});
                relation["amenity"="university"](area:{$areaId});
            );
            out center tags;
        OVERPASS;
        $lastError = null;

        foreach ($this->overpassEndpoints as $endpoint) {
            try {
                Log::info('Overpass request START', ['endpoint' => $endpoint, 'area_id' => $areaId]);
                $startedAt = microtime(true);

                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout(180)->asForm()->post($endpoint, ['data' => $overpassQuery]);

                $duration = round(microtime(true) - $startedAt, 3);
                Log::info('Overpass request COMPLETE', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'duration_seconds' => $duration,
                ]);

                if (! $response->successful()) {
                    $lastError = "HTTP {$response->status()}";
                    Log::warning('Overpass request FAILED', [
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    continue;
                }

                $elements = $response->json('elements') ?? [];
                Log::info('Overpass elements received', ['count' => count($elements)]);

                return $this->parseElements($elements, $state, $country);
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                Log::error('Overpass request EXCEPTION', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
            }
        }

        Log::error('All Overpass endpoints FAILED', ['last_error' => $lastError]);
        throw new Exception("All Overpass endpoints failed. Last error: {$lastError}");
    }

    public function parseElements(array $elements, ?string $state = null, ?string $country = null): array
    {
        $records = [];
        $seenExact = [];
        $seenNameAddress = [];
        $seenNameLocation = [];

        $duplicateCount = 0;
        $invalidCount = 0;
        $validationFailedCount = 0;
        $validationPassedCount = 0;

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $type = strtolower((string) ($el['type'] ?? 'node'));
            $osmId = isset($el['id']) ? (string) $el['id'] : null;
            $lat = $el['lat'] ?? $el['center']['lat'] ?? null;
            $lon = $el['lon'] ?? $el['center']['lon'] ?? null;

            if (! $this->isValidUniversityTags($tags)) {
                $invalidCount++;
                Log::debug(
                    'Overpass record rejected - invalid university tags',
                    [
                        'osm_type' => $type,
                        'osm_id' => $osmId,
                        'name' => $tags['name'] ?? null,
                        'amenity' => $tags['amenity'] ?? null,
                    ]
                );

                continue;
            }

            $name = trim((string) ($tags['name'] ?? ''));
            if ($name === '') {
                $invalidCount++;

                continue;
            }

            if ($lat === null || $lon === null) {
                $invalidCount++;
                Log::debug(
                    'Overpass record rejected - missing coordinates',
                    [
                        'osm_type' => $type,
                        'osm_id' => $osmId,
                        'name' => $name,
                    ]
                );

                continue;
            }

            $lat = (float) $lat;
            $lon = (float) $lon;
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
            ], function ($value) {
                return $value !== null && trim((string) $value) !== '';
            });

            $address = ! empty($addressParts) ? implode(', ', $addressParts) : null;
            $normalizedName = $this->normalize($name);
            $normalizedAddress = $this->normalize($address ?? '');
            $normalizedCity = $this->normalize($tags['addr:city'] ?? '');

            if ($osmId !== null) {
                $exactKey = "{$type}:{$osmId}";

                if (isset($seenExact[$exactKey])) {
                    $duplicateCount++;
                    Log::debug(
                        'Overpass duplicate removed - exact OSM object',
                        [
                            'osm_type' => $type,
                            'osm_id' => $osmId,
                            'name' => $name,
                        ]
                    );

                    continue;
                }

                $seenExact[$exactKey] = true;
            }

            if ($normalizedAddress !== '') {
                $nameAddressKey = implode('|', [$normalizedName, $normalizedAddress]);
                if (isset($seenNameAddress[$nameAddressKey])) {
                    $duplicateCount++;
                    Log::debug(
                        'Overpass duplicate removed - name/address',
                        [
                            'osm_type' => $type,
                            'osm_id' => $osmId,
                            'name' => $name,
                            'address' => $address,
                        ]
                    );

                    continue;
                }

                $seenNameAddress[$nameAddressKey] = true;
            }

            $roundedLat = round($lat, 4);
            $roundedLon = round($lon, 4);
            $nameLocationKey = implode('|', [$normalizedName, $normalizedCity, $roundedLat, $roundedLon]);

            if (isset($seenNameLocation[$nameLocationKey])) {
                $duplicateCount++;
                Log::debug(
                    'Overpass duplicate removed - name/location',
                    [
                        'osm_type' => $type,
                        'osm_id' => $osmId,
                        'name' => $name,
                        'latitude' => $lat,
                        'longitude' => $lon,
                    ]
                );

                continue;
            }

            $seenNameLocation[$nameLocationKey] = true;
            $validation = $this->validateWithNominatim(
                $name,
                $lat,
                $lon,
                $osmId,
                $type
            );

            if (! $validation['valid']) {
                $validationFailedCount++;
                Log::info(
                    'University rejected by Nominatim',
                    [
                        'osm_type' => $type,
                        'osm_id' => $osmId,
                        'name' => $name,
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'score' => $validation['score'],
                        'reason' => $validation['reason'] ?? null,
                        'nominatim_name' => $validation['name'] ?? null,
                        'nominatim_type' => $validation['type'] ?? null,
                        'nominatim_category' => $validation['category'] ?? null,
                    ]
                );

                continue;
            }

            $validationPassedCount++;
            $records[] = [
                'osm_id' => $osmId,
                'osm_type' => $type,
                'name' => $name,
                'type' => 'university',
                'latitude' => $lat,
                'longitude' => $lon,
                'address' => $address,
                'city' => $tags['addr:city'] ?? null,
                'state' => $elementState,
                'country' => $elementCountry,
                'postcode' => $tags['addr:postcode'] ?? null,
                'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
                'nominatim_valid' => true,
                'nominatim_score' => $validation['score'],
                'nominatim_name' => $validation['name'],
                'nominatim_type' => $validation['type'],
                'nominatim_category' => $validation['category'],
                'nominatim_name_similarity' => $validation['name_similarity'] ?? null,
                'nominatim_distance_meters' => $validation['distance_meters'] ?? null,
            ];
        }

        Log::info('University processing COMPLETE', [
            'input_count' => count($elements),
            'output_count' => count($records),
            'duplicates_removed' => $duplicateCount,
            'invalid_osm_records' => $invalidCount,
            'nominatim_validation_failed' => $validationFailedCount,
            'nominatim_validation_passed' => $validationPassedCount,
        ]);

        return $records;
    }

    protected function validateWithNominatim(
        string $name,
        float $lat,
        float $lon,
        ?string $osmId,
        string $osmType
    ): array {
        $cacheKey = implode(':', [$osmType, $osmId ?? '', round($lat, 5), round($lon, 5)]);

        if (isset($this->nominatimCache[$cacheKey])) {
            Log::info(
                'Nominatim validation CACHE HIT',
                [
                    'osm_type' => $osmType,
                    'osm_id' => $osmId,
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lon,
                ]
            );

            return $this->nominatimCache[$cacheKey];
        }

        Log::info(
            'Nominatim validation START',
            [
                'endpoint' => $this->nominatimEndpoint,
                'osm_type' => $osmType,
                'osm_id' => $osmId,
                'overpass_name' => $name,
                'latitude' => $lat,
                'longitude' => $lon,
            ]
        );

        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->get(
                    $this->nominatimEndpoint,
                    [
                        'lat' => $lat,
                        'lon' => $lon,
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                        'zoom' => 18,
                    ]
                );

            $duration = round(microtime(true) - $startedAt, 3);

            Log::info(
                'Nominatim HTTP response',
                [
                    'osm_type' => $osmType,
                    'osm_id' => $osmId,
                    'overpass_name' => $name,
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'duration_seconds' => $duration,
                ]
            );

            if (! $response->successful()) {
                Log::warning(
                    'Nominatim validation FAILED - HTTP error',
                    [
                        'osm_type' => $osmType,
                        'osm_id' => $osmId,
                        'name' => $name,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'duration_seconds' => $duration,
                    ]
                );

                return $this->cacheValidationResult(
                    $cacheKey,
                    [
                        'valid' => false,
                        'score' => 0,
                        'name' => null,
                        'type' => null,
                        'category' => null,
                        'reason' => 'nominatim_http_error',
                    ]
                );
            }

            $data = $response->json();

            Log::info(
                'Nominatim validation RESULT',
                [
                    'osm_type' => $osmType,
                    'osm_id' => $osmId,
                    'overpass_name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'nominatim_result' => $data,
                    'duration_seconds' => $duration,
                ]
            );

            if (! is_array($data)) {
                return $this->cacheValidationResult(
                    $cacheKey,
                    [
                        'valid' => false,
                        'score' => 0,
                        'name' => null,
                        'type' => null,
                        'category' => null,
                        'reason' => 'invalid_nominatim_response',
                    ]
                );
            }

            $nominatimName = trim((string) ($data['name'] ?? ''));
            $nominatimType = strtolower(trim((string) ($data['type'] ?? '')));
            $nominatimCategory = strtolower(trim((string) ($data['category'] ?? '')));
            $nominatimOsmId = isset($data['osm_id']) ? (string) $data['osm_id'] : null;
            $nominatimOsmType = isset($data['osm_type']) ? strtolower((string) $data['osm_type']) : null;

            $score = 0;

            $isUniversityType = $nominatimType === 'university' || ($nominatimCategory === 'amenity' && $nominatimType === 'university');

            if ($isUniversityType) {
                $score += 3;
            }

            $nameSimilarity = $this->nameSimilarity($name, $nominatimName);

            if ($nameSimilarity >= 70) {
                $score += 2;
            } elseif ($nameSimilarity >= 50) {
                $score += 1;
            }

            $sameOsmObject = $osmId !== null && $nominatimOsmId !== null && $osmId === $nominatimOsmId && $osmType === $nominatimOsmType;
            if ($sameOsmObject) {
                $score += 3;
            }

            $distance = null;

            $nominatimLat = isset($data['lat']) ? (float) $data['lat'] : null;
            $nominatimLon = isset($data['lon']) ? (float) $data['lon'] : null;
            if ($nominatimLat !== null && $nominatimLon !== null) {
                $distance = $this->distanceInMeters(
                    $lat,
                    $lon,
                    $nominatimLat,
                    $nominatimLon
                );

                if ($distance <= 100) {
                    $score += 2;
                }
            }
            $valid = $score >= $this->minimumValidationScore;
            $reason = 'score_below_threshold';
            $nonUniversityTypes = ['restaurant', 'cafe', 'bar', 'pub', 'parking', 'fuel', 'hotel', 'motel', 'hospital', 'clinic', 'pharmacy', 'school', 'kindergarten', 'fast_food', 'boat', 'marina', 'parking_entrance', 'museum', 'park', 'stadium', 'theatre'];

            if (in_array($nominatimType, $nonUniversityTypes, true) && $nameSimilarity < 50 && ! $sameOsmObject) {
                $valid = false;
                $reason = 'nominatim_identifies_non_university';
            }

            if ($valid) {
                $reason = 'validation_passed';
            }
            $result = [
                'valid' => $valid,
                'score' => $score,
                'reason' => $reason,
                'name' => $nominatimName,
                'type' => $nominatimType,
                'category' => $nominatimCategory,
                'name_similarity' => $nameSimilarity,
                'osm_id' => $nominatimOsmId,
                'osm_type' => $nominatimOsmType,
                'distance_meters' => $distance,
            ];
            Log::info(
                'Nominatim validation COMPLETE',
                [
                    'osm_type' => $osmType,
                    'osm_id' => $osmId,
                    'overpass_name' => $name,
                    'nominatim_name' => $nominatimName,
                    'nominatim_type' => $nominatimType,
                    'nominatim_category' => $nominatimCategory,
                    'nominatim_osm_type' => $nominatimOsmType,
                    'nominatim_osm_id' => $nominatimOsmId,
                    'name_similarity' => $nameSimilarity,
                    'distance_meters' => $distance,
                    'same_osm_object' => $sameOsmObject,
                    'is_university_type' => $isUniversityType,
                    'score' => $score,
                    'minimum_score' => $this->minimumValidationScore,
                    'valid' => $valid,
                    'reason' => $reason,
                    'duration_seconds' => $duration,
                ]
            );

            return $this->cacheValidationResult($cacheKey, $result);
        } catch (Exception $e) {
            $duration = round(microtime(true) - $startedAt, 3);
            Log::error(
                'Nominatim validation EXCEPTION',
                [
                    'osm_type' => $osmType,
                    'osm_id' => $osmId,
                    'overpass_name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'duration_seconds' => $duration,
                ]
            );

            return $this->cacheValidationResult(
                $cacheKey,
                [
                    'valid' => false,
                    'score' => 0,
                    'name' => null,
                    'type' => null,
                    'category' => null,
                    'reason' => 'nominatim_exception',
                ]
            );
        }
    }

    protected function isValidUniversityTags(array $tags): bool
    {
        $amenity = strtolower(
            trim((string) ($tags['amenity'] ?? ''))
        );

        if ($amenity !== 'university') {
            return false;
        }

        $name = trim((string) ($tags['name'] ?? ''));
        if ($name === '') {
            return false;
        }

        return true;
    }

    protected function nameSimilarity(string $name1, string $name2): float
    {
        $name1 = $this->normalize($name1);
        $name2 = $this->normalize($name2);

        if ($name1 === '' || $name2 === '') {
            return 0;
        }

        if ($name1 === $name2) {
            return 100;
        }

        $name1Comparable = $this->removeCommonWords($name1);
        $name2Comparable = $this->removeCommonWords($name2);

        if ($name1Comparable !== '' && $name1Comparable === $name2Comparable) {
            return 100;
        }

        similar_text($name1, $name2, $percent);

        return (float) $percent;
    }

    protected function removeCommonWords(string $value): string
    {
        $words = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
        $ignored = ['the', 'of', 'at', 'and', 'university', 'universities', 'college', 'campus'];
        $words = array_filter($words, fn ($word) => ! in_array($word, $ignored, true));

        return implode(' ', $words);
    }

    protected function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $value = mb_strtolower($value);
        $value = preg_replace('/[^\pL\pN\s]/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    protected function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);
        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    protected function cacheValidationResult(string $key, array $result): array
    {
        $this->nominatimCache[$key] = $result;

        return $result;
    }
}
