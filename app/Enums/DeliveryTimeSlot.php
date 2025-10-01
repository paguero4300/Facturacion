<?php

namespace App\Enums;

enum DeliveryTimeSlot: string
{
    case MORNING = 'morning';
    case AFTERNOON = 'afternoon';
    case EVENING = 'evening';

    public function label(): string
    {
        return match($this) {
            self::MORNING => 'Mañana (9:00 - 12:00)',
            self::AFTERNOON => 'Tarde (14:00 - 17:00)',
            self::EVENING => 'Noche (18:00 - 20:00)',
        };
    }

    public function timeRange(): string
    {
        return match($this) {
            self::MORNING => '9:00 - 12:00',
            self::AFTERNOON => '14:00 - 17:00',
            self::EVENING => '18:00 - 20:00',
        };
    }

    public function isAvailableOnDay(string $dayOfWeek): bool
    {
        // El horario de noche solo está disponible de lunes a viernes
        if ($this === self::EVENING) {
            return !in_array($dayOfWeek, ['Saturday', 'Sunday']);
        }
        
        // Mañana y tarde disponibles de lunes a sábado
        return !in_array($dayOfWeek, ['Sunday']);
    }

    public static function availableForDate(\Carbon\Carbon $date): array
    {
        $dayOfWeek = $date->format('l'); // Monday, Tuesday, etc.
        
        return array_filter(self::cases(), function($slot) use ($dayOfWeek) {
            return $slot->isAvailableOnDay($dayOfWeek);
        });
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }
}