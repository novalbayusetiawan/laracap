<?php

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/admin/sync-update', function () {
    if (! auth()->user()?->is_admin) {
        abort(403);
    }

    $output = [];
    exec('git pull origin main 2>&1', $output);
    Artisan::call('migrate', ['--force' => true]);

    Notification::make()
        ->title('System Updated')
        ->body('Updated successfully.')
        ->success()
        ->send();

    return redirect()->back();
})->name('admin.sync-update')->middleware(['auth']);

require __DIR__.'/auth.php';
