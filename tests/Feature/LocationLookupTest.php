<?php

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can fetch countries list via api', function () {
    Country::create([
        'name' => 'United States',
        'phone_code' => '1',
    ]);
    Country::create([
        'name' => 'Canada',
        'phone_code' => '1',
    ]);

    $response = $this->getJson('/locations/countries?query=United');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    $response->assertJsonFragment([
        'name' => 'United States',
    ]);
    $response->assertJsonMissing([
        'name' => 'Canada',
    ]);
});

test('can fetch states list filtered by country_id', function () {
    $us = Country::create(['name' => 'United States']);
    $ca = Country::create(['name' => 'Canada']);

    $california = State::create([
        'name' => 'California',
        'state_code' => 'CA',
        'country_id' => $us->id,
    ]);
    $ontario = State::create([
        'name' => 'Ontario',
        'state_code' => 'ON',
        'country_id' => $ca->id,
    ]);

    $response = $this->getJson("/locations/states?country_id={$us->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'name' => 'California',
    ]);
    $response->assertJsonMissing([
        'name' => 'Ontario',
    ]);
});

test('can fetch cities list filtered by state_id', function () {
    $us = Country::create(['name' => 'United States']);
    $california = State::create([
        'name' => 'California',
        'state_code' => 'CA',
        'country_id' => $us->id,
    ]);

    City::create([
        'name' => 'Sacramento',
        'state_id' => $california->id,
    ]);
    City::create([
        'name' => 'Los Angeles',
        'state_id' => $california->id,
    ]);

    $response = $this->getJson("/locations/cities?state_id={$california->id}&query=Sacra");

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'name' => 'Sacramento',
    ]);
    $response->assertJsonMissing([
        'name' => 'Los Angeles',
    ]);
});
