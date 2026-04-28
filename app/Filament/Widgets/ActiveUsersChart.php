<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ActiveUsersChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Daily Active Users';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = Auth::user();

        $data = [];
        $labels = [];

        // Last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $query = Device::whereDate('last_active_at', $date->toDateString());

            if (! $user->is_admin) {
                $query->whereHas('bundle.application', fn ($q) => $q->where('user_id', $user->id));
            }

            $data[] = $query->count();
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
