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
