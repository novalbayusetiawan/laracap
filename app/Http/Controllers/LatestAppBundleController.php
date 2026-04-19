<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Device;
use Illuminate\Http\Request;

class LatestAppBundleController extends Controller
{
    public function __invoke(Application $application, Request $request)
    {
        $deviceIdentifier = $request->input('device_identifier') ?? $request->header('X-Device-Identifier');
        $platform = $request->input('platform') ?? $request->header('X-Platform');
        $bundleId = $request->input('bundle_id') ?? $request->header('X-Bundle-Id') ?? $application->latestBundle->id;

        if ($deviceIdentifier && $platform) {
            Device::updateOrCreate(
                ['device_identifier' => $deviceIdentifier],
                [
                    'platform' => $platform,
                    'bundle_id' => $bundleId,
                    'last_active_at' => now(),
                ]
            );
        }

        return response()->json($application->latestBundle);
    }
}

