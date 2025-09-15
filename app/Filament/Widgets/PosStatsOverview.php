<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PosStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');
        $thisWeek = [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')];
        
        // Ventas de hoy
        $todaySales = Invoice::whereDate('issue_date', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
            
        // Tickets de hoy
        $todayTickets = Invoice::whereDate('issue_date', $today)
            ->where('status', '!=', 'cancelled')
            ->count();
            
        // Promedio por ticket hoy
        $avgTicket = $todayTickets > 0 ? $todaySales / $todayTickets : 0;
        
        // Ventas de la semana
        $weekSales = Invoice::whereBetween('issue_date', $thisWeek)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
            
        // Productos con stock bajo
        $lowStockProducts = Product::where('track_inventory', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Ventas Hoy', 'S/ ' . number_format($todaySales, 2))
                ->description('Total vendido hoy')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Tickets Hoy', $todayTickets)
                ->description('Documentos emitidos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
                
            Stat::make('Promedio/Ticket', 'S/ ' . number_format($avgTicket, 2))
                ->description('Ticket promedio hoy')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
                
            Stat::make('Ventas Semana', 'S/ ' . number_format($weekSales, 2))
                ->description('Total de la semana')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
                
            Stat::make('Stock Bajo', $lowStockProducts)
                ->description('Productos con stock mínimo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),
        ];
    }
}