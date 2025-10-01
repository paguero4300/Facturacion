<?php

namespace App\Enums;

enum PaymentValidationStatus: string
{
    case PENDING_VALIDATION = 'pending_validation';
    case PAYMENT_APPROVED = 'payment_approved';
    case PAYMENT_REJECTED = 'payment_rejected';
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case VALIDATION_NOT_REQUIRED = 'validation_not_required';

    public function label(): string
    {
        return match($this) {
            self::PENDING_VALIDATION => 'Pendiente de Validación',
            self::PAYMENT_APPROVED => 'Pago Aprobado',
            self::PAYMENT_REJECTED => 'Pago Rechazado',
            self::CASH_ON_DELIVERY => 'Efectivo contra Entrega',
            self::VALIDATION_NOT_REQUIRED => 'No Requiere Validación',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING_VALIDATION => 'warning',
            self::PAYMENT_APPROVED => 'success',
            self::PAYMENT_REJECTED => 'danger',
            self::CASH_ON_DELIVERY => 'info',
            self::VALIDATION_NOT_REQUIRED => 'gray',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PENDING_VALIDATION => 'heroicon-o-clock',
            self::PAYMENT_APPROVED => 'heroicon-o-check-circle',
            self::PAYMENT_REJECTED => 'heroicon-o-x-circle',
            self::CASH_ON_DELIVERY => 'heroicon-o-banknotes',
            self::VALIDATION_NOT_REQUIRED => 'heroicon-o-minus-circle',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING_VALIDATION => 'El comprobante de pago ha sido enviado y está esperando revisión del administrador',
            self::PAYMENT_APPROVED => 'El pago ha sido verificado y aprobado por el administrador',
            self::PAYMENT_REJECTED => 'El comprobante fue rechazado, se requiere enviar nueva evidencia',
            self::CASH_ON_DELIVERY => 'Pago en efectivo programado para la entrega',
            self::VALIDATION_NOT_REQUIRED => 'Este método de pago no requiere validación manual',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return [$case->value => $case->label()];
        })->toArray();
    }

    public function requiresEvidence(): bool
    {
        return match($this) {
            self::PENDING_VALIDATION,
            self::PAYMENT_APPROVED,
            self::PAYMENT_REJECTED => true,
            default => false,
        };
    }

    public function canTransitionTo(PaymentValidationStatus $newStatus): bool
    {
        return match($this) {
            self::PENDING_VALIDATION => in_array($newStatus, [
                self::PAYMENT_APPROVED,
                self::PAYMENT_REJECTED
            ]),
            self::PAYMENT_REJECTED => in_array($newStatus, [
                self::PENDING_VALIDATION,
                self::PAYMENT_APPROVED
            ]),
            self::PAYMENT_APPROVED => false, // No se puede cambiar una vez aprobado
            self::CASH_ON_DELIVERY => in_array($newStatus, [
                self::PAYMENT_APPROVED // Solo cuando se confirma la entrega
            ]),
            self::VALIDATION_NOT_REQUIRED => false,
        };
    }
}