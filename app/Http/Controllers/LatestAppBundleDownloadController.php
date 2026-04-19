<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Device;
use Illuminate\Http\Request;

class LatestAppBundleDownloadController extends Controller
{
    public function __invoke(Application $application, Request $request)
    {
        $deviceIdentifier = $request->input('device_identifier') ?? $request->header('X-Device-Identifier');
        $platform = $request->input('platform') ?? $request->header('X-Platform');
        // When downloading the latest, we might optionally imply they are on this version now or just update active time.
        $bundleId = $request->input('bundle_id') ?? $request->header('X-Bundle-Id') ?? $application->latestBundle?->id;

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

        return response()->download(storage_path('app/public/' . $application->latestBundle->file_path));
    }
}

