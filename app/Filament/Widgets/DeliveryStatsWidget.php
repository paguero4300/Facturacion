<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Enums\DeliveryStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeliveryStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        // Today's deliveries
        $todayDeliveries = Invoice::withDeliveryScheduled()
            ->byDeliveryDate($today)
            ->get();
            
        // Tomorrow's deliveries
        $tomorrowDeliveries = Invoice::withDeliveryScheduled()
            ->byDeliveryDate($tomorrow)
            ->get();
            
        // Pending deliveries (programmed but not delivered)
        $pendingDeliveries = Invoice::withDeliveryScheduled()
            ->whereIn('delivery_status', [DeliveryStatus::PROGRAMADO, DeliveryStatus::EN_RUTA])
            ->count();
            
        // Overdue deliveries
        $overdueDeliveries = Invoice::deliveryOverdue()->count();
        
        return [
            Stat::make('Entregas Hoy', $todayDeliveries->count())
                ->description('Entregas programadas para hoy')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($todayDeliveries->count() > 0 ? 'warning' : 'success')
                ->chart($this->getTodayChart($todayDeliveries)),
                
            Stat::make('Entregas Mañana', $tomorrowDeliveries->count())
                ->description('Entregas programadas para mañana')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
                
            Stat::make('Pendientes', $pendingDeliveries)
                ->description('Entregas por realizar')
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingDeliveries > 10 ? 'warning' : 'success'),
                
            Stat::make('Vencidas', $overdueDeliveries)
                ->description('Entregas vencidas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueDeliveries > 0 ? 'danger' : 'success'),
        ];
    }
    
    private function getTodayChart($deliveries): array
    {
        $statusCounts = [
            DeliveryStatus::PROGRAMADO->value => 0,
            DeliveryStatus::EN_RUTA->value => 0,
            DeliveryStatus::ENTREGADO->value => 0,
            DeliveryStatus::REPROGRAMADO->value => 0,
        ];
        
        foreach ($deliveries as $delivery) {
            if ($delivery->delivery_status) {
                $statusCounts[$delivery->delivery_status->value]++;
            }
        }
        
        return array_values($statusCounts);
    }
}
