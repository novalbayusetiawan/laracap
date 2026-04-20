<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Bundle;
use App\Models\Device;
use Illuminate\Http\Request;

class LatestAppBundleController extends Controller
{
    public function __invoke(Application $application, Request $request)
    {
        $deviceIdentifier = $request->input('device_identifier') ?? $request->header('X-Device-Identifier');
        $platform         = $request->input('platform') ?? $request->header('X-Platform');
        $currentBundleId  = $request->input('bundle_id') ?? $request->header('X-Bundle-Id');
        $channelName      = $request->input('channel') ?? $request->header('X-Channel');

        if ($deviceIdentifier && $platform) {
            $bundleId = is_numeric($currentBundleId) ? $currentBundleId : null;

            if ($bundleId && ! Bundle::where('id', $bundleId)->exists()) {
                $bundleId = null;
            }

            Device::updateOrCreate(
                ['device_identifier' => $deviceIdentifier],
                [
                    'platform'       => $platform,
                    'bundle_id'      => $bundleId,
                    'last_active_at' => now(),
                ]
            );
        }

        $channel      = $application->channels()->where('name', $channelName)->first();
        $latestBundle = $channel
            ? $channel->bundles()->latest()->first()
            :  null;

        $isUpdateAvailable = false;

        if ($latestBundle) {
            if (!$currentBundleId || $latestBundle->id > (int) $currentBundleId) {
                $isUpdateAvailable = true;
            }
        }

        $currentBundle = is_numeric($currentBundleId) ? Bundle::find($currentBundleId) : null;

        return response()->json([
            'is_update_available' => $isUpdateAvailable,
            'latest_bundle'       => $latestBundle,
            'current_bundle'      => $currentBundle,
            'download_url'        => $latestBundle ? route('latest-app-bundle-download', ['application' => $application, 'channel' => $channelName]) : null,
        ]);
    }
}

