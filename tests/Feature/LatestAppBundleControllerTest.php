<?php

use App\Models\Application;
use App\Models\Bundle;
use App\Models\Channel;

it('determines if update is available based on per-channel bundle id', function () {
    $application = Application::factory()->create();
    $prodChannel = Channel::factory()->create([
        'application_id' => $application->id,
        'name' => 'production',
    ]);
    $stagChannel = Channel::factory()->create([
        'application_id' => $application->id,
        'name' => 'staging',
    ]);

    // Create bundles: staging bundle ID will be higher than production bundle ID
    $prodBundle = Bundle::factory()->create([
        'application_id' => $application->id,
        'channel_id' => $prodChannel->id,
    ]);

    $stagBundle = Bundle::factory()->create([
        'application_id' => $application->id,
        'channel_id' => $stagChannel->id,
    ]);

    // Scenario: The device is currently on the staging bundle ($stagBundle->id).
    // It checks the production channel.
    // If we only send X-Bundle-Id as staging bundle ID, but X-Channel-Bundle-Id is empty/null,
    // it should say an update is available (to the production bundle).
    $response = $this->withHeaders([
        'X-Device-Identifier' => 'device-123',
        'X-Platform' => 'ios',
        'X-Bundle-Id' => $stagBundle->id,
        'X-Channel' => 'production',
    ])->getJson(route('latest-app-bundle', ['application' => $application->uuid]));

    $response->assertStatus(200)
        ->assertJson([
            'is_update_available' => true,
            'latest_bundle' => [
                'id' => $prodBundle->id,
            ],
        ]);

    // Scenario: The device checks production channel again, but now sends X-Channel-Bundle-Id matching the production bundle.
    // It should report no update available.
    $response = $this->withHeaders([
        'X-Device-Identifier' => 'device-123',
        'X-Platform' => 'ios',
        'X-Bundle-Id' => $stagBundle->id,
        'X-Channel-Bundle-Id' => $prodBundle->id,
        'X-Channel' => 'production',
    ])->getJson(route('latest-app-bundle', ['application' => $application->uuid]));

    $response->assertStatus(200)
        ->assertJson([
            'is_update_available' => false,
        ]);
});
