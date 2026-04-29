<?php

namespace App\Filament\Widgets;

 use App\Models\Bundle;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BundlesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Recent Bundle Uploads';

    protected function getData(): array
    {
        $user = Auth::user();

        $data = [];
        $labels = [];

        // Last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M');

            $query = Bundle::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year);

            if (! $user->is_admin) {
                $query->whereHas('application', fn ($q) => $q->where('user_id', $user->id));
            }

            $data[] = $query->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bundles uploaded',
                    'data' => $data,
                    'fill' => 'start',
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
