<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Bundle;
use App\Traits\TracksDevice;
use Illuminate\Http\Request;

class LatestAppBundleController extends Controller
{
    use TracksDevice;

    public function __invoke(Application $application, Request $request)
    {
        $deviceIdentifier = $request->input('device_identifier') ?? $request->header('X-Device-Identifier');
        $platform = $request->input('platform') ?? $request->header('X-Platform');
        $currentBundleId = $request->input('bundle_id') ?? $request->header('X-Bundle-Id');
        $channelName = $request->input('channel') ?? $request->header('X-Channel');

        // Per-channel bundle ID: the bundle the device last applied for THIS channel.
        // Falls back to the global active bundle ID for backwards compatibility.
        $channelBundleId = $request->input('channel_bundle_id')
            ?? $request->header('X-Channel-Bundle-Id')
            ?? $currentBundleId;

        if ($deviceIdentifier && $platform) {
            $bundleId = is_numeric($currentBundleId) ? $currentBundleId : null;

            if ($bundleId && ! Bundle::where('id', $bundleId)->exists()) {
                $bundleId = null;
            }

            $this->trackDevice($request, $deviceIdentifier, $platform, $bundleId, $application->id, 'check');
        }

        $channel = $application->channels()->where('name', $channelName)->first();
        $latestBundle = $channel
            ? $channel->bundles()->latest()->first()
            : null;

        $isUpdateAvailable = false;

        if ($latestBundle) {
            // Compare using the channel-specific bundle ID instead of the global one.
            // Use != so that channel switches AND rollbacks are both detected.
            if (! $channelBundleId || (string) $latestBundle->id !== (string) $channelBundleId) {
                $isUpdateAvailable = true;
            }
        }

        $currentBundle = is_numeric($currentBundleId) ? Bundle::find($currentBundleId) : null;

        return response()->json([
            'is_update_available' => $isUpdateAvailable,
            'latest_bundle' => $latestBundle,
            'current_bundle' => $currentBundle,
            'download_url' => $latestBundle ? route('latest-app-bundle-download', ['application' => $application, 'channel' => $channelName]) : null,
        ]);
    }
}
