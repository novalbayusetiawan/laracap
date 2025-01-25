<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Bundle;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Applications', Application::count())
                ->icon('heroicon-o-squares-2x2'),
            Stat::make('Total Bundles', Bundle::count())
                ->icon('heroicon-o-cube'),
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-user-circle'),
        ];
    }
}
