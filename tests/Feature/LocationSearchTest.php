<?php

use App\Models\LocationSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('location search redirects back when query is empty', function () {
    $response = $this->post('/institutions/search', [
        'location_query' => '',
    ]);

    $response->assertRedirect();
});

test('location search queries existing search from database cache', function () {
    $search = LocationSearch::create([
        'query' => 'Austin, Texas',
        'display_name' => 'Austin, Travis County, Texas, United States',
        'area_id' => 3600111112,
        'place_name' => 'Austin',
        'state' => 'Texas',
        'country' => 'United States',
        'total_found' => 0,
        'searched_at' => now(),
    ]);

    $response = $this->post('/institutions/search', [
        'location_query' => 'Austin, Texas',
    ]);

    $response->assertRedirect(route('institutions.index'));
    $response->assertSessionHas('searchResult');
});

test('location search combines country state city inputs if location_query is omitted', function () {
    $search = LocationSearch::create([
        'query' => 'Dallas, Texas, United States',
        'display_name' => 'Dallas, Dallas County, Texas, United States',
        'area_id' => 3600111115,
        'place_name' => 'Dallas',
        'state' => 'Texas',
        'country' => 'United States',
        'total_found' => 0,
        'searched_at' => now(),
    ]);

    $response = $this->post('/institutions/search', [
        'country' => 'United States',
        'state' => 'Texas',
        'city' => 'Dallas',
    ]);

    $response->assertRedirect(route('institutions.index'));
    $response->assertSessionHas('locationQuery', 'Dallas, Texas, United States');
    $response->assertSessionHas('searchResult');
});
