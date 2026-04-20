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
        $platform         = $request->input('platform') ?? $request->header('X-Platform');
        $channelName      = $request->input('channel') ?? $request->header('X-Channel');

        $channel      = $application->channels()->where('name', $channelName)->first();
        $latestBundle = $channel
            ? $channel->bundles()->latest()->first()
            :  null;

        if ($deviceIdentifier && $platform) {
            Device::updateOrCreate(
                ['device_identifier' => $deviceIdentifier],
                [
                    'platform'       => $platform,
                    'bundle_id'      => $latestBundle?->id,
                    'last_active_at' => now(),
                ]
            );
        }

        if (!$latestBundle) {
            return response()->json(['message' => 'No bundle found'], 404);
        }

        return response()->download(storage_path('app/public/' . $latestBundle->file_path), null, [
            'X-Bundle-Id'   => $latestBundle->id,
            'X-Bundle-Uuid' => $latestBundle->uuid,
        ]);
    }
}

