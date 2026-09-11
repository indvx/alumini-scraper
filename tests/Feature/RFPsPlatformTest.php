<?php

use App\Models\RFPsPlatform;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rfps platform index page renders successfully', function () {
    $response = $this->get('/rfps-platform');

    $response->assertStatus(200);
    $response->assertSee('RFPs Platforms');
});

test('rfps platform create page renders successfully', function () {
    $response = $this->get('/rfps-platform/create');

    $response->assertStatus(200);
    $response->assertSee('Add New RFP Platform');
});

test('can create new rfp platform', function () {
    $response = $this->post('/rfps-platform', [
        'name' => 'Texas Procurement Portal',
        'domain' => 'txsmartbuy.com',
        'url' => 'https://www.txsmartbuy.com',
        'platform_type' => 'Government Portal',
        'country' => 'United States',
        'state' => 'Texas',
        'city' => 'Austin',
        'institution_type' => 'State Government',
        'coverage' => 'Statewide',
        'status' => 'active',
        'is_public' => '1',
    ]);

    $response->assertRedirect('/rfps-platform');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('rfps_platforms', [
        'name' => 'Texas Procurement Portal',
        'domain' => 'txsmartbuy.com',
        'status' => 'active',
        'is_public' => true,
    ]);
});

test('rfps platform show page renders successfully', function () {
    $platform = RFPsPlatform::create([
        'name' => 'California Contracts Register',
        'domain' => 'caleprocure.ca.gov',
        'url' => 'https://caleprocure.ca.gov',
        'platform_type' => 'State Portal',
        'country' => 'United States',
        'state' => 'California',
        'city' => 'Sacramento',
        'status' => 'active',
    ]);

    $response = $this->get("/rfps-platform/{$platform->id}");

    $response->assertStatus(200);
    $response->assertSee('California Contracts Register');
    $response->assertSee('caleprocure.ca.gov');
});

test('rfps platform edit page renders successfully', function () {
    $platform = RFPsPlatform::create([
        'name' => 'NY State eProcurement',
        'status' => 'active',
    ]);

    $response = $this->get("/rfps-platform/{$platform->id}/edit");

    $response->assertStatus(200);
    $response->assertSee('Edit RFP Platform');
    $response->assertSee('NY State eProcurement');
});

test('can update rfp platform', function () {
    $platform = RFPsPlatform::create([
        'name' => 'Old Platform Name',
        'status' => 'active',
    ]);

    $response = $this->put("/rfps-platform/{$platform->id}", [
        'name' => 'Updated Platform Name',
        'domain' => 'updated.gov',
        'status' => 'inactive',
    ]);

    $response->assertRedirect("/rfps-platform/{$platform->id}");
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('rfps_platforms', [
        'id' => $platform->id,
        'name' => 'Updated Platform Name',
        'status' => 'inactive',
    ]);
});

test('can delete rfp platform', function () {
    $platform = RFPsPlatform::create([
        'name' => 'Platform To Delete',
        'status' => 'active',
    ]);

    $response = $this->delete("/rfps-platform/{$platform->id}");

    $response->assertRedirect('/rfps-platform');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('rfps_platforms', [
        'id' => $platform->id,
    ]);
});

test('rfps platform csv export streams content', function () {
    $response = $this->get('/rfps-platform/export/csv');

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('can search rfps platforms using ai search', function () {
    RFPsPlatform::create([
        'name' => 'California Higher Ed Procurement',
        'state' => 'California',
        'status' => 'active',
    ]);

    $response = $this->get('/rfps-platform/ai-search?ai_prompt=Active+California+university+portals');

    $response->assertStatus(200);
    $response->assertSee('AI Search Applied');
    $response->assertSee('California Higher Ed Procurement');
});
