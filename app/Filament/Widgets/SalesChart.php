<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Carbon\CarbonPeriod;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Ventas de la Semana';
    }

    protected function getData(): array
    {
        $data = Invoice::query()
            ->whereBetween('issue_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('DATE(issue_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Fill missing days
        $results = [];
        $period = CarbonPeriod::create(now()->startOfWeek(), now()->endOfWeek());
        
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $found = $data->firstWhere('date', $dateString);
            $results[$dateString] = $found ? $found->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => array_values($results),
                    'backgroundColor' => 'rgba(244, 63, 94, 0.2)',
                    'borderColor' => '#f43f5e',
                    'fill' => 'start',
                ],
            ],
            'labels' => array_keys($results),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
