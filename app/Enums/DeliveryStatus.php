<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PROGRAMADO = 'programado';
    case EN_RUTA = 'en_ruta';
    case ENTREGADO = 'entregado';
    case REPROGRAMADO = 'reprogramado';

    public function label(): string
    {
        return match($this) {
            self::PROGRAMADO => 'Programado',
            self::EN_RUTA => 'En Ruta',
            self::ENTREGADO => 'Entregado',
            self::REPROGRAMADO => 'Reprogramado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PROGRAMADO => 'info',
            self::EN_RUTA => 'warning',
            self::ENTREGADO => 'success',
            self::REPROGRAMADO => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PROGRAMADO => 'heroicon-o-calendar',
            self::EN_RUTA => 'heroicon-o-truck',
            self::ENTREGADO => 'heroicon-o-check-circle',
            self::REPROGRAMADO => 'heroicon-o-arrow-path',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PROGRAMADO => 'Entrega planificada para la fecha y horario indicados',
            self::EN_RUTA => 'El pedido está en proceso de entrega',
            self::ENTREGADO => 'Pedido entregado exitosamente',
            self::REPROGRAMADO => 'Requiere nueva programación de fecha y horario',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }

    public function canTransitionTo(DeliveryStatus $newStatus): bool
    {
        return match($this) {
            self::PROGRAMADO => in_array($newStatus, [self::EN_RUTA, self::REPROGRAMADO]),
            self::EN_RUTA => in_array($newStatus, [self::ENTREGADO, self::REPROGRAMADO]),
            self::REPROGRAMADO => in_array($newStatus, [self::PROGRAMADO]),
            self::ENTREGADO => false, // Estado final
        };
    }
}