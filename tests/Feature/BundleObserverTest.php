<?php

use App\Models\Application;
use App\Models\Bundle;

it('prunes old bundles exceeding the application bundle limit', function () {
    $application = Application::factory()->create([
        'bundle_limit' => 2,
    ]);

    // Create 3 bundles sequentially
    $bundle1 = Bundle::factory()->create([
        'application_id' => $application->id,
        'created_at' => now()->subMinutes(10),
    ]);

    $bundle2 = Bundle::factory()->create([
        'application_id' => $application->id,
        'created_at' => now()->subMinutes(5),
    ]);

    $bundle3 = Bundle::factory()->create([
        'application_id' => $application->id,
        'created_at' => now(),
    ]);

    // The observer should have run when bundle3 was created
    // Verify bundle1 (oldest) has been deleted, while bundle2 and bundle3 remain
    expect(Bundle::where('id', $bundle1->id)->exists())->toBeFalse();
    expect(Bundle::where('id', $bundle2->id)->exists())->toBeTrue();
    expect(Bundle::where('id', $bundle3->id)->exists())->toBeTrue();
});
