<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PlatformDistributionChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Platform Distribution';

    protected function getData(): array
    {
        $user = Auth::user();
        
        $query = Device::query();

        if (!$user->is_admin) {
            $query->whereHas('bundle.application', fn ($q) => $q->where('user_id', $user->id));
        }

        $distribution = $query->selectRaw('platform, count(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform');

        return [
            'datasets' => [
                [
                    'label' => 'Devices',
                    'data' => [
                        $distribution->get('ios', 0),
                        $distribution->get('android', 0),
                        $distribution->get('web', 0),
                    ],
                    'backgroundColor' => [
                        '#94a3b8', // Gray for iOS
                        '#22c55e', // Green for Android
                        '#3b82f6', // Blue for Web
                    ],
                ],
            ],
            'labels' => ['iOS', 'Android', 'Web'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
