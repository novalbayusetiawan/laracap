<?php

namespace App\Http\Controllers;

use App\Models\Application;

class LatestAppBundleController extends Controller
{
    public function __invoke(Application $application)
    {
        return response()->json($application->latestBundle);
    }
}

