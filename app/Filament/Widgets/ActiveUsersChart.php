<?php

namespace App\Filament\Widgets;

use App\Models\DeviceLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ActiveUsersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Daily Active Users (Daily Unique)';

    protected function getData(): array
    {
        $user = Auth::user();

        $data = [];
        $labels = [];

        // Last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $query = DeviceLog::whereDate('created_at', $date->toDateString());

            if (! $user->is_admin) {
                $query->whereHas('application', fn ($q) => $q->where('user_id', $user->id));
            }

            $data[] = $query->distinct('device_id')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Users',
                    'data' => $data,
                    'fill' => 'start',
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
