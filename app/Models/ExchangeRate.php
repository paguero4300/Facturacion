<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'buy_rate',
        'sell_rate',
        'source',
        'raw_data',
        'fetched_at',
    ];

    protected $casts = [
        'date' => 'date',
        'buy_rate' => 'decimal:6',
        'sell_rate' => 'decimal:6',
        'raw_data' => 'array',
        'fetched_at' => 'datetime',
    ];

    /**
     * Obtener el tipo de cambio para una fecha específica
     */
    public static function getForDate(?string $date = null, string $source = 'factiliza'): ?self
    {
        $date = $date ?: now()->toDateString();
        
        return static::where('date', $date)
            ->where('source', $source)
            ->first();
    }

    /**
     * Obtener el tipo de cambio más reciente
     */
    public static function getLatest(string $source = 'factiliza'): ?self
    {
        return static::where('source', $source)
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Verificar si existe tipo de cambio para hoy
     */
    public static function existsForToday(string $source = 'factiliza'): bool
    {
        return static::where('date', now()->toDateString())
            ->where('source', $source)
            ->exists();
    }

    /**
     * Crear o actualizar tipo de cambio
     */
    public static function createOrUpdate(array $data, ?string $date = null, string $source = 'factiliza'): self
    {
        $date = $date ?: now()->toDateString();
        
        return static::updateOrCreate(
            [
                'date' => $date,
                'source' => $source,
            ],
            [
                'buy_rate' => $data['compra'] ?? $data['buy_rate'] ?? 0,
                'sell_rate' => $data['venta'] ?? $data['sell_rate'] ?? 0,
                'raw_data' => $data,
                'fetched_at' => now(),
            ]
        );
    }

    /**
     * Limpiar tipos de cambio antiguos (más de 30 días)
     */
    public static function cleanOldRates(int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep)->toDateString();
        
        return static::where('date', '<', $cutoffDate)->delete();
    }

    /**
     * Obtener estadísticas de tipos de cambio
     */
    public static function getStats(int $days = 7): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        $rates = static::where('date', '>=', $startDate)
            ->orderBy('date', 'desc')
            ->get();
            
        if ($rates->isEmpty()) {
            return [
                'count' => 0,
                'avg_buy' => 0,
                'avg_sell' => 0,
                'min_buy' => 0,
                'max_buy' => 0,
                'min_sell' => 0,
                'max_sell' => 0,
                'latest' => null,
            ];
        }
        
        return [
            'count' => $rates->count(),
            'avg_buy' => $rates->avg('buy_rate'),
            'avg_sell' => $rates->avg('sell_rate'),
            'min_buy' => $rates->min('buy_rate'),
            'max_buy' => $rates->max('buy_rate'),
            'min_sell' => $rates->min('sell_rate'),
            'max_sell' => $rates->max('sell_rate'),
            'latest' => $rates->first(),
        ];
    }
}