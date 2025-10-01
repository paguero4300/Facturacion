<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DeliveryValidationService
{
    /**
     * Holidays list (add your specific holidays here)
     * Format: 'MM-DD' for annual holidays, 'YYYY-MM-DD' for specific dates
     */
    private array $holidays = [
        '01-01', // Año Nuevo
        '05-01', // Día del Trabajador
        '07-28', // Día de la Independencia
        '07-29', // Día de la Independencia (segundo día)
        '08-30', // Santa Rosa de Lima
        '10-08', // Combate de Angamos
        '11-01', // Todos los Santos
        '12-08', // Inmaculada Concepción
        '12-25', // Navidad
        // Add specific year holidays if needed
        // '2025-04-17', // Jueves Santo 2025
        // '2025-04-18', // Viernes Santo 2025
    ];

    /**
     * Check if a date is a valid delivery date
     */
    public function isValidDeliveryDate(Carbon $date): bool
    {
        // Check if it's a Sunday
        if ($date->isSunday()) {
            return false;
        }

        // Check if it's a holiday
        if ($this->isHoliday($date)) {
            return false;
        }

        // Check if it's at least tomorrow
        if ($date->isBefore(Carbon::tomorrow())) {
            return false;
        }

        // Check if it's within 30 days
        if ($date->isAfter(Carbon::now()->addDays(30))) {
            return false;
        }

        return true;
    }

    /**
     * Check if a date is a holiday
     */
    public function isHoliday(Carbon $date): bool
    {
        $monthDay = $date->format('m-d');
        $fullDate = $date->format('Y-m-d');

        return in_array($monthDay, $this->holidays) || in_array($fullDate, $this->holidays);
    }

    /**
     * Get the next available delivery date
     */
    public function getNextAvailableDate(): Carbon
    {
        $date = Carbon::tomorrow();
        
        while (!$this->isValidDeliveryDate($date)) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * Get all available delivery dates for the next N days
     */
    public function getAvailableDates(int $days = 30): Collection
    {
        $dates = collect();
        $currentDate = Carbon::tomorrow();
        $endDate = Carbon::now()->addDays($days);

        while ($currentDate->isBefore($endDate)) {
            if ($this->isValidDeliveryDate($currentDate)) {
                $dates->push($currentDate->copy());
            }
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * Get validation error message for invalid date
     */
    public function getValidationMessage(Carbon $date): string
    {
        if ($date->isBefore(Carbon::tomorrow())) {
            return 'La entrega debe programarse con al menos 1 día de anticipación.';
        }

        if ($date->isAfter(Carbon::now()->addDays(30))) {
            return 'Solo se pueden programar entregas hasta 30 días en el futuro.';
        }

        if ($date->isSunday()) {
            return 'Las entregas no están disponibles los domingos.';
        }

        if ($this->isHoliday($date)) {
            return 'Las entregas no están disponibles en días festivos.';
        }

        return 'La fecha seleccionada no está disponible para entregas.';
    }

    /**
     * Add a new holiday
     */
    public function addHoliday(string $date): void
    {
        if (!in_array($date, $this->holidays)) {
            $this->holidays[] = $date;
        }
    }

    /**
     * Remove a holiday
     */
    public function removeHoliday(string $date): void
    {
        $this->holidays = array_diff($this->holidays, [$date]);
    }

    /**
     * Get all configured holidays
     */
    public function getHolidays(): array
    {
        return $this->holidays;
    }
}