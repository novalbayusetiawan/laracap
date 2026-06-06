# Channel-Switching & Update Comparison Architecture

This document outlines the update comparison logic for OTA updates and how it supports channel-switching and channel-less bundles.

---

## 1. Request Handling

The update endpoint (`/api/applications/{application}/bundles/latest`) receives headers/parameters from the device describing its state:
- `X-Bundle-Id` / `bundle_id`: The global active bundle ID running on the device.
- `X-Channel-Bundle-Id` / `channel_bundle_id`: The bundle ID the device last applied for the requested channel.
- `X-Channel` / `channel`: The deployment channel being checked (e.g. `production`, `staging`).

---

## 2. Update Decision Flow

In `LatestAppBundleController`, the update availability is determined as follows:

```php
// 1. Resolve channel-specific bundle ID
$channelBundleId = $request->input('channel_bundle_id')
    ?? $request->header('X-Channel-Bundle-Id')
    ?? $currentBundleId; // Fallback to global active bundle ID

// 2. Resolve latest bundle for the requested channel
$channel = $application->channels()->where('name', $channelName)->first();
$latestBundle = $channel ? $channel->bundles()->latest()->first() : null;

// 3. Compare within the channel using inequality (!=)
$isUpdateAvailable = false;
if ($latestBundle) {
    if (! $channelBundleId || (string) $latestBundle->id !== (string) $channelBundleId) {
        $isUpdateAvailable = true;
    }
}
```

---

## 3. Key Design Features

### A. Channel-Specific Isolation
By comparing the latest channel bundle against `X-Channel-Bundle-Id` (instead of the global active `X-Bundle-Id`), the server prevents cross-channel blockages.
* Example: A device running staging bundle `8` checking the `production` channel (where the latest is `5`) will report `X-Channel-Bundle-Id: 5`. The server sees `5 === 5` (no update available). If the device reports `X-Channel-Bundle-Id: null` (never synced production), the server correctly says `is_update_available: true` (update to `5` is available).

### B. Rollback Support
Using inequality (`!==`) instead of greater-than (`>`) allows the server to trigger a "downgrade" update if a bad bundle is retracted on the server and replaced by an older version.

### C. Manual / Channel-Less Bundles Fallback
If a device has loaded a manual bundle (set via `setBundle()` without channel association) and performs a channel sync, `X-Channel-Bundle-Id` will be missing. The controller falls back to the global `X-Bundle-Id`, allowing it to successfully detect that the running bundle is different from the channel's latest bundle and trigger a sync.
