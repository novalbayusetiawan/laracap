<?php

namespace App\Observers;

use App\Models\Bundle;
use Illuminate\Support\Facades\Storage;

class BundleObserver
{
    /**
     * Handle the Bundle "created" event.
     */
    public function created(Bundle $bundle): void
    {
        $application = $bundle->application;

        if ($application && $application->bundle_limit) {
            $bundlesToDelete = $application->bundles()
                ->orderBy('created_at', 'desc')
                ->skip($application->bundle_limit)
                ->get();

            foreach ($bundlesToDelete as $oldBundle) {
                $oldBundle->delete();
            }
        }
    }

    /**
     * Handle the Bundle "deleted" event.
     */
    public function deleted(Bundle $bundle): void
    {
        if ($bundle->file_path && Storage::disk('public')->exists($bundle->file_path)) {
            Storage::disk('public')->delete($bundle->file_path);
        }
    }
}
