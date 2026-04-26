<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Bundle;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Channel;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $isAdmin = $user?->is_admin;

        $appsQuery = Application::query();
        $channelsQuery = Channel::query();
        $devicesQuery = Device::query();

        if (!$isAdmin) {
            $appsQuery->where('user_id', $user->id);
            $channelsQuery->whereHas('application', fn ($q) => $q->where('user_id', $user->id));
            $devicesQuery->whereHas('bundle.application', fn ($q) => $q->where('user_id', $user->id));
        }

        $stats = [
            Stat::make('Applications', $appsQuery->count())
                ->description('Total managed apps')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-squares-2x2'),
            Stat::make('Channels', $channelsQuery->count())
                ->description('Deployment channels')
                ->icon('heroicon-o-signal'),
            Stat::make('Active Devices', $devicesQuery->count())
                ->description('Devices with your bundles')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('info')
                ->icon('heroicon-o-cpu-chip'),
            // create stat of bundle sizes
            Stat::make('Bundle Sizes', function() use ($user) {
                $size = Bundle::query()->whereHas('application', fn ($q) => $q->where('user_id', $user->id))
                ->sum('size');
                return \Illuminate\Support\Number::fileSize($size, precision: 2);
            })
            ->descriptionIcon('heroicon-m-device-phone-mobile')
            ->color('info')
            ->icon('heroicon-o-cpu-chip'),
        ];

        if ($isAdmin) {
            $stats[] = Stat::make('Total Users', User::count())
                ->description('Platform users')
                ->icon('heroicon-o-user-circle')
                ->color('primary');
        }

        return $stats;
    }
}
