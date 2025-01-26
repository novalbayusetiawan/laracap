<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class LatestAppBundleController extends Controller
{
    public function __invoke(Application $application)
    {
        return response()->json($application->latestBundle);
    }
}

