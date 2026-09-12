<?php

use App\Models\Institution;
use App\Models\RFPsPlatform;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can attach rfp platform to institution with relationship metadata', function () {
    $institution = Institution::create([
        'name' => 'University of Texas at Austin',
        'type' => 'university',
    ]);

    $platform = RFPsPlatform::create([
        'name' => 'Bonfire',
        'domain' => 'gobonfire.com',
        'status' => 'active',
    ]);

    $response = $this->post("/institutions/{$institution->id}/rfp-platforms", [
        'rfps_platform_id' => $platform->id,
        'confidence' => 98,
        'status' => 'active',
        'discovery_method' => 'AI + Web Search',
        'source_title' => 'University procurement page',
        'source_url' => 'https://procurement.utexas.edu/bids',
        'first_verified_at' => '2026-09-12',
        'last_verified_at' => '2026-09-12',
        'notes' => 'Formal bid opportunities are posted through Bonfire.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('institution_rfp_platform', [
        'institution_id' => $institution->id,
        'rfps_platform_id' => $platform->id,
        'confidence' => 98,
        'status' => 'active',
        'discovery_method' => 'AI + Web Search',
        'source_title' => 'University procurement page',
        'source_url' => 'https://procurement.utexas.edu/bids',
        'notes' => 'Formal bid opportunities are posted through Bonfire.',
    ]);
});

test('institution show page displays attached rfp platform and relationship details', function () {
    $institution = Institution::create([
        'name' => 'University of Texas at Austin',
        'type' => 'university',
    ]);

    $platform = RFPsPlatform::create([
        'name' => 'Bonfire',
        'domain' => 'gobonfire.com',
        'status' => 'active',
    ]);

    $institution->rfpPlatforms()->attach($platform->id, [
        'confidence' => 98,
        'status' => 'active',
        'discovery_method' => 'AI + Web Search',
        'source_title' => 'University procurement page',
        'source_url' => 'https://procurement.utexas.edu/bids',
        'notes' => 'Formal bid opportunities are posted through Bonfire.',
    ]);

    $response = $this->get("/institutions/{$institution->id}");

    $response->assertStatus(200);
    $response->assertSee('Associated RFP Platforms');
    $response->assertSee('Bonfire');
    $response->assertSee('University of Texas at Austin');
    $response->assertSee('uses');
    $response->assertSee('Confidence: 98%');
    $response->assertSee('AI + Web Search');
    $response->assertSee('University procurement page');
    $response->assertSee('Visit Evidence');
    $response->assertSee('Formal bid opportunities are posted through Bonfire.');
});

test('can detach rfp platform from institution', function () {
    $institution = Institution::create([
        'name' => 'Stanford University',
        'type' => 'university',
    ]);

    $platform = RFPsPlatform::create([
        'name' => 'California State Contracts Register',
        'status' => 'active',
    ]);

    $institution->rfpPlatforms()->attach($platform->id);

    $response = $this->delete("/institutions/{$institution->id}/rfp-platforms/{$platform->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('institution_rfp_platform', [
        'institution_id' => $institution->id,
        'rfps_platform_id' => $platform->id,
    ]);
});

test('can attach institution to rfp platform with relationship metadata', function () {
    $platform = RFPsPlatform::create([
        'name' => 'TxSmartBuy',
        'status' => 'active',
    ]);

    $institution = Institution::create([
        'name' => 'Texas A&M University',
        'type' => 'university',
    ]);

    $response = $this->post("/rfps-platform/{$platform->id}/institutions", [
        'institution_id' => $institution->id,
        'confidence' => 95,
        'status' => 'active',
        'discovery_method' => 'Manual',
        'source_title' => 'State Portal Directory',
        'source_url' => 'https://txsmartbuy.com/directory',
        'notes' => 'Primary procurement platform for Texas state agencies.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('institution_rfp_platform', [
        'institution_id' => $institution->id,
        'rfps_platform_id' => $platform->id,
        'confidence' => 95,
        'discovery_method' => 'Manual',
    ]);
});

test('rfp platform show page displays attached institution and relationship details', function () {
    $platform = RFPsPlatform::create([
        'name' => 'Bonfire',
        'domain' => 'gobonfire.com',
        'status' => 'active',
    ]);

    $institution = Institution::create([
        'name' => 'University of Texas at Austin',
        'type' => 'university',
    ]);

    $platform->institutions()->attach($institution->id, [
        'confidence' => 98,
        'status' => 'active',
        'discovery_method' => 'AI + Web Search',
        'source_title' => 'University procurement page',
        'source_url' => 'https://procurement.utexas.edu/bids',
        'notes' => 'Formal bid opportunities are posted through Bonfire.',
    ]);

    $response = $this->get("/rfps-platform/{$platform->id}");

    $response->assertStatus(200);
    $response->assertSee('Associated Institutions');
    $response->assertSee('University of Texas at Austin');
    $response->assertSee('uses');
    $response->assertSee('Bonfire');
    $response->assertSee('Confidence: 98%');
    $response->assertSee('Visit Evidence');
});

test('can detach institution from rfp platform', function () {
    $platform = RFPsPlatform::create([
        'name' => 'Generic RFP Portal',
        'status' => 'active',
    ]);

    $institution = Institution::create([
        'name' => 'Community College',
        'type' => 'college',
    ]);

    $platform->institutions()->attach($institution->id);

    $response = $this->delete("/rfps-platform/{$platform->id}/institutions/{$institution->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('institution_rfp_platform', [
        'institution_id' => $institution->id,
        'rfps_platform_id' => $platform->id,
    ]);
});

test('institution show page limits initial rfp platform suggestions to 10 items', function () {
    $institution = Institution::create([
        'name' => 'Test Institution',
        'type' => 'university',
    ]);

    for ($i = 1; $i <= 15; $i++) {
        RFPsPlatform::create([
            'name' => "Platform {$i}",
            'status' => 'active',
        ]);
    }

    $response = $this->get("/institutions/{$institution->id}");
    $response->assertStatus(200);

    $platforms = $response->viewData('allRfpPlatforms');
    expect($platforms)->toHaveCount(10);
});

test('rfp platform show page limits initial institution suggestions to 10 items', function () {
    $platform = RFPsPlatform::create([
        'name' => 'Test RFP Platform',
        'status' => 'active',
    ]);

    for ($i = 1; $i <= 15; $i++) {
        Institution::create([
            'name' => "Institution {$i}",
            'type' => 'school',
        ]);
    }

    $response = $this->get("/rfps-platform/{$platform->id}");
    $response->assertStatus(200);

    $institutions = $response->viewData('allInstitutions');
    expect($institutions)->toHaveCount(10);
});

test('rfp platform search api returns at most 10 matching platforms', function () {
    for ($i = 1; $i <= 15; $i++) {
        RFPsPlatform::create([
            'name' => "Procurement Portal {$i}",
            'status' => 'active',
        ]);
    }

    $response = $this->getJson('/rfps-platform/search-api/lookup?query=Portal');
    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect($data)->toHaveCount(10);
});

test('institution search api returns at most 10 matching institutions', function () {
    for ($i = 1; $i <= 15; $i++) {
        Institution::create([
            'name' => "State Academy {$i}",
            'type' => 'school',
        ]);
    }

    $response = $this->getJson('/institutions/search-api/lookup?query=Academy');
    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $data = $response->json('data');
    expect($data)->toHaveCount(10);
});

test('rfp platform show page filters institution suggestions and search by platform country', function () {
    $usPlatform = RFPsPlatform::create([
        'name' => 'USA Government Bids',
        'country' => 'USA',
        'status' => 'active',
    ]);

    $usInst = Institution::create([
        'name' => 'Harvard University',
        'type' => 'university',
        'country' => 'USA',
    ]);

    $ukInst = Institution::create([
        'name' => 'Oxford University',
        'type' => 'university',
        'country' => 'UK',
    ]);

    $response = $this->get("/rfps-platform/{$usPlatform->id}");
    $response->assertStatus(200);

    $suggested = $response->viewData('allInstitutions');
    expect($suggested->pluck('id'))->toContain($usInst->id);
    expect($suggested->pluck('id'))->not->toContain($ukInst->id);

    $apiResponse = $this->getJson("/institutions/search-api/lookup?query=University&country=USA");
    $apiResponse->assertStatus(200);
    $apiIds = collect($apiResponse->json('data'))->pluck('id');
    expect($apiIds)->toContain($usInst->id);
    expect($apiIds)->not->toContain($ukInst->id);
});

test('institution show page filters rfp platform suggestions and search by institution country', function () {
    $usInst = Institution::create([
        'name' => 'MIT',
        'type' => 'university',
        'country' => 'USA',
    ]);

    $usPlatform = RFPsPlatform::create([
        'name' => 'Bonfire Bids US',
        'country' => 'USA',
        'status' => 'active',
    ]);

    $globalPlatform = RFPsPlatform::create([
        'name' => 'Global Procurement Net',
        'country' => null,
        'status' => 'active',
    ]);

    $caPlatform = RFPsPlatform::create([
        'name' => 'Canada Bids',
        'country' => 'Canada',
        'status' => 'active',
    ]);

    $response = $this->get("/institutions/{$usInst->id}");
    $response->assertStatus(200);

    $suggested = $response->viewData('allRfpPlatforms');
    expect($suggested->pluck('id'))->toContain($usPlatform->id);
    expect($suggested->pluck('id'))->toContain($globalPlatform->id);
    expect($suggested->pluck('id'))->not->toContain($caPlatform->id);

    $apiResponse = $this->getJson("/rfps-platform/search-api/lookup?query=Bids&country=USA");
    $apiResponse->assertStatus(200);
    $apiIds = collect($apiResponse->json('data'))->pluck('id');
    expect($apiIds)->toContain($usPlatform->id);
    expect($apiIds)->not->toContain($caPlatform->id);
});

test('institution country falls back to location search country when null on institution model', function () {
    $search = \App\Models\LocationSearch::create([
        'query' => 'Austin TX',
        'display_name' => 'Austin, Travis County, Texas, United States',
        'country' => 'United States',
    ]);

    $instWithoutCountry = Institution::create([
        'name' => 'Austin Community College',
        'type' => 'college',
        'country' => null,
        'search_id' => $search->id,
    ]);

    expect($instWithoutCountry->country)->toBe('United States');
});

test('institution scopeByCountry matches institution via search country fallback', function () {
    $search = \App\Models\LocationSearch::create([
        'query' => 'Dallas TX',
        'display_name' => 'Dallas, Dallas County, Texas, USA',
        'country' => 'USA',
    ]);

    $inst = Institution::create([
        'name' => 'Dallas Academy',
        'type' => 'school',
        'country' => null,
        'search_id' => $search->id,
    ]);

    $found = Institution::byCountry('USA')->get();
    expect($found->pluck('id'))->toContain($inst->id);
});
