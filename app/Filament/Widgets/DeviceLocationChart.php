<?php

namespace App\Filament\Widgets;

use App\Models\DeviceLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class DeviceLocationChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Top 10 Device Locations';

    protected function getData(): array
    {
        $user = Auth::user();

        $query = DeviceLog::query()
            ->selectRaw('city, country, count(distinct device_id) as device_count')
            ->whereNotNull('city')
            ->whereNotNull('country')
            ->groupBy('city', 'country')
            ->orderByDesc('device_count')
            ->limit(10);

        if (! $user->is_admin) {
            $query->whereHas('application', fn ($q) => $q->where('user_id', $user->id));
        }

        $results = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Devices',
                    'data' => $results->pluck('device_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                        '#ec4899', '#06b6d4', '#f97316', '#14b8a6', '#6366f1',
                    ],
                ],
            ],
            'labels' => $results->map(fn ($r) => "{$r->city}, {$r->country}")->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
