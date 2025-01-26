<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\LatestAppBundleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('applications', [ApplicationController::class, 'index'])
        ->name('applications.index');

    Route::post('bundles', [BundleController::class, 'store'])
        ->name('create-app-bundle');
});


Route::get('applications/{application}/bundles/latest', LatestAppBundleController::class)
    ->name('latest-app-bundle');


Route::post('login', [AuthTokenController::class, 'store'])
    ->name('login');

Route::delete('logout', [AuthTokenController::class, 'destroy'])
    ->name('logout');
