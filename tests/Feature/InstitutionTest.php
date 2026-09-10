<?php

use App\Models\Institution;
use App\Models\LocationSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page renders successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Alumni');
});

test('dashboard renders successfully', function () {
    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Application Dashboard');
});

test('institutions index page renders successfully', function () {
    $response = $this->get('/institutions');

    $response->assertStatus(200);
    $response->assertSee('Institutions List');
});

test('institutions detail page renders successfully', function () {
    $search = LocationSearch::create([
        'query' => 'Dallas, Texas',
        'display_name' => 'Dallas, Dallas County, Texas, United States',
        'area_id' => 3600111111,
        'place_name' => 'Dallas',
        'state' => 'Texas',
        'country' => 'United States',
        'total_found' => 1,
        'searched_at' => now(),
    ]);

    $institution = Institution::create([
        'search_id' => $search->id,
        'name' => 'Test High School',
        'type' => 'school',
        'latitude' => 32.7767,
        'longitude' => -96.7970,
        'address' => '123 Main St, Dallas, TX',
        'city' => 'Dallas',
        'state' => 'Texas',
        'postcode' => '75201',
    ]);

    $response = $this->get("/institutions/{$institution->id}");

    $response->assertStatus(200);
    $response->assertSee('Test High School');
    $response->assertSee('Map Location');
});

test('api institution index returns paginated data', function () {
    $response = $this->getJson('/api/institutions');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'total',
        'page',
        'page_size',
        'total_pages',
        'items',
    ]);
});

test('csv export streams valid CSV content', function () {
    $response = $this->get('/institutions/export/csv');

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
