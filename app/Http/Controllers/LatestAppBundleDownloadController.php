<?php

namespace App\Http\Controllers;

use App\Models\Application;

class LatestAppBundleDownloadController extends Controller
{
    public function __invoke(Application $application)
    {
        return response()->download(storage_path('app/public/' . $application->latestBundle->file_path));
    }
}

