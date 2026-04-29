<?php

namespace App\Traits;

use App\Jobs\TrackDeviceJob;
use Illuminate\Http\Request;

trait TracksDevice
{
    protected function trackDevice(Request $request, string $deviceIdentifier, string $platform, ?int $bundleId = null, ?int $applicationId = null, string $type = 'check'): void
    {
        TrackDeviceJob::dispatch(
            $deviceIdentifier,
            $platform,
            $request->ip(),
            $request->userAgent(),
            $bundleId,
            $applicationId,
            $type
        );
    }
}
