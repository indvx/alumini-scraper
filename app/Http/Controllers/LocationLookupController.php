<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationLookupController extends Controller
{
    /**
     * Get countries list / search suggestions.
     */
    public function getCountries(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));

        $builder = Country::query();

        if ($query !== '') {
            $builder->where('name', 'like', "%{$query}%");
        }

        $countries = $builder->orderBy('name', 'asc')
            ->limit(50)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $countries,
        ]);
    }

    /**
     * Get states list for a selected country.
     */
    public function getStates(Request $request): JsonResponse
    {
        $countryId = $request->input('country_id');
        $countryName = trim((string) $request->input('country', ''));
        $query = trim((string) $request->input('query', ''));

        if (! $countryId && $countryName !== '') {
            $country = Country::where('name', $countryName)->first();
            $countryId = $country?->id;
        }

        if (! $countryId) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $builder = State::where('country_id', $countryId);

        if ($query !== '') {
            $builder->where('name', 'like', "%{$query}%");
        }

        $states = $builder->orderBy('name', 'asc')
            ->limit(100)
            ->get(['id', 'name', 'state_code']);

        return response()->json([
            'success' => true,
            'data' => $states,
        ]);
    }

    /**
     * Get cities list for a selected state.
     */
    public function getCities(Request $request): JsonResponse
    {
        $stateId = $request->input('state_id');
        $stateName = trim((string) $request->input('state', ''));
        $countryId = $request->input('country_id');
        $countryName = trim((string) $request->input('country', ''));
        $query = trim((string) $request->input('query', ''));

        if (! $stateId && $stateName !== '') {
            $stateQuery = State::where('name', $stateName);

            if ($countryId) {
                $stateQuery->where('country_id', $countryId);
            } elseif ($countryName !== '') {
                $country = Country::where('name', $countryName)->first();
                if ($country) {
                    $stateQuery->where('country_id', $country->id);
                }
            }

            $state = $stateQuery->first();
            $stateId = $state?->id;
        }

        if (! $stateId) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $builder = City::where('state_id', $stateId);

        if ($query !== '') {
            $builder->where('name', 'like', "%{$query}%");
        }

        $cities = $builder->orderBy('name', 'asc')
            ->limit(100)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }
}
