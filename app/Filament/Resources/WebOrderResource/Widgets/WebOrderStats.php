<?php

namespace App\Filament\Resources\WebOrderResource\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebOrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        $query = Invoice::where('series', 'NV02');

        $pendingOrders = (clone $query)->where('status', 'draft')->count();
        $completedOrders = (clone $query)->where('status', 'paid')->count();
        $cancelledOrders = (clone $query)->where('status', 'cancelled')->count();
        $totalRevenue = (clone $query)->where('status', 'paid')->sum('total_amount');

        // Payment validation stats
        $pendingValidation = (clone $query)
            ->where('payment_validation_status', \App\Enums\PaymentValidationStatus::PENDING_VALIDATION)
            ->count();

        $withoutEvidence = (clone $query)
            ->whereIn('payment_validation_status', [
                \App\Enums\PaymentValidationStatus::PENDING_VALIDATION,
                \App\Enums\PaymentValidationStatus::PAYMENT_REJECTED
            ])
            ->whereNull('payment_evidence_path')
            ->count();

        // Orders this month
        $thisMonth = (clone $query)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Orders last month for comparison
        $lastMonth = (clone $query)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyGrowth = $lastMonth > 0
            ? (($thisMonth - $lastMonth) / $lastMonth) * 100
            : ($thisMonth > 0 ? 100 : 0);

        return [
            Stat::make('Pedidos Pendientes', $pendingOrders)
                ->description('Esperando procesamiento')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Pagos Pendientes de Validación', $pendingValidation)
                ->description('Requieren revisión urgente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->chart([2, 3, 5, 4, 6, 5, 7, 6]),

            Stat::make('Pedidos Sin Comprobante', $withoutEvidence)
                ->description('Seguir por WhatsApp')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('danger')
                ->chart([1, 2, 3, 2, 4, 3, 5, 4]),

            Stat::make('Pedidos Completados', $completedOrders)
                ->description('Entregados exitosamente')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([3, 5, 4, 6, 7, 8, 6, 9]),

            Stat::make('Pedidos Este Mes', $thisMonth)
                ->description($monthlyGrowth >= 0
                    ? sprintf('%+.1f%% vs mes anterior', $monthlyGrowth)
                    : sprintf('%.1f%% vs mes anterior', $monthlyGrowth)
                )
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger')
                ->chart([3, 4, 5, 6, 7, 8, 9, 10]),

            Stat::make('Ingresos Totales', 'S/ ' . number_format($totalRevenue, 2))
                ->description('Pedidos completados')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->chart([5, 10, 8, 12, 15, 18, 20, 22]),
        ];
    }
}
