<?php

use App\Enums\DeliveryTimeSlot;
use App\Enums\DeliveryStatus;
use App\Services\DeliveryValidationService;
use Carbon\Carbon;

test('delivery time slot labels are correct', function () {
    expect(DeliveryTimeSlot::MORNING->label())->toBe('Mañana (9:00 - 12:00)');
    expect(DeliveryTimeSlot::AFTERNOON->label())->toBe('Tarde (14:00 - 17:00)');
    expect(DeliveryTimeSlot::EVENING->label())->toBe('Noche (18:00 - 20:00)');
});

test('delivery time slot ranges are correct', function () {
    expect(DeliveryTimeSlot::MORNING->timeRange())->toBe('9:00 - 12:00');
    expect(DeliveryTimeSlot::AFTERNOON->timeRange())->toBe('14:00 - 17:00');
    expect(DeliveryTimeSlot::EVENING->timeRange())->toBe('18:00 - 20:00');
});

test('delivery time slots availability by day', function () {
    // Monday - all slots available
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay('Monday'))->toBeTrue();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay('Monday'))->toBeTrue();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay('Monday'))->toBeTrue();
    
    // Saturday - no evening slot
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay('Saturday'))->toBeTrue();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay('Saturday'))->toBeTrue();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay('Saturday'))->toBeFalse();
    
    // Sunday - no slots available
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay('Sunday'))->toBeFalse();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay('Sunday'))->toBeFalse();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay('Sunday'))->toBeFalse();
});

test('delivery status labels and colors are correct', function () {
    expect(DeliveryStatus::PROGRAMADO->label())->toBe('Programado');
    expect(DeliveryStatus::EN_RUTA->label())->toBe('En Ruta');
    expect(DeliveryStatus::ENTREGADO->label())->toBe('Entregado');
    expect(DeliveryStatus::REPROGRAMADO->label())->toBe('Reprogramado');
    
    expect(DeliveryStatus::PROGRAMADO->color())->toBe('info');
    expect(DeliveryStatus::EN_RUTA->color())->toBe('warning');
    expect(DeliveryStatus::ENTREGADO->color())->toBe('success');
    expect(DeliveryStatus::REPROGRAMADO->color())->toBe('danger');
});

test('delivery status transitions are validated correctly', function () {
    // From PROGRAMADO
    expect(DeliveryStatus::PROGRAMADO->canTransitionTo(DeliveryStatus::EN_RUTA))->toBeTrue();
    expect(DeliveryStatus::PROGRAMADO->canTransitionTo(DeliveryStatus::REPROGRAMADO))->toBeTrue();
    expect(DeliveryStatus::PROGRAMADO->canTransitionTo(DeliveryStatus::ENTREGADO))->toBeFalse();
    
    // From EN_RUTA
    expect(DeliveryStatus::EN_RUTA->canTransitionTo(DeliveryStatus::ENTREGADO))->toBeTrue();
    expect(DeliveryStatus::EN_RUTA->canTransitionTo(DeliveryStatus::REPROGRAMADO))->toBeTrue();
    expect(DeliveryStatus::EN_RUTA->canTransitionTo(DeliveryStatus::PROGRAMADO))->toBeFalse();
    
    // From ENTREGADO (final state)
    expect(DeliveryStatus::ENTREGADO->canTransitionTo(DeliveryStatus::EN_RUTA))->toBeFalse();
    expect(DeliveryStatus::ENTREGADO->canTransitionTo(DeliveryStatus::PROGRAMADO))->toBeFalse();
    expect(DeliveryStatus::ENTREGADO->canTransitionTo(DeliveryStatus::REPROGRAMADO))->toBeFalse();
    
    // From REPROGRAMADO
    expect(DeliveryStatus::REPROGRAMADO->canTransitionTo(DeliveryStatus::PROGRAMADO))->toBeTrue();
    expect(DeliveryStatus::REPROGRAMADO->canTransitionTo(DeliveryStatus::EN_RUTA))->toBeFalse();
});

test('delivery validation service validates dates correctly', function () {
    $service = new DeliveryValidationService();
    
    // Valid date (tomorrow, assuming it's not Sunday)
    $tomorrow = Carbon::tomorrow();
    if (!$tomorrow->isSunday()) {
        expect($service->isValidDeliveryDate($tomorrow))->toBeTrue();
    }
    
    // Invalid date (yesterday)
    $yesterday = Carbon::yesterday();
    expect($service->isValidDeliveryDate($yesterday))->toBeFalse();
    
    // Invalid date (today)
    $today = Carbon::today();
    expect($service->isValidDeliveryDate($today))->toBeFalse();
    
    // Invalid date (too far in future)
    $tooFar = Carbon::now()->addDays(31);
    expect($service->isValidDeliveryDate($tooFar))->toBeFalse();
});

test('delivery validation service identifies sundays correctly', function () {
    $service = new DeliveryValidationService();
    
    // Find next Sunday
    $sunday = Carbon::now()->next(Carbon::SUNDAY);
    expect($service->isValidDeliveryDate($sunday))->toBeFalse();
    
    // Find next Monday (should be valid if within range)
    $monday = Carbon::now()->next(Carbon::MONDAY);
    if ($monday->isBefore(Carbon::now()->addDays(30))) {
        expect($service->isValidDeliveryDate($monday))->toBeTrue();
    }
});

test('delivery validation service provides correct error messages', function () {
    $service = new DeliveryValidationService();
    
    // Past date
    $pastDate = Carbon::yesterday();
    $message = $service->getValidationMessage($pastDate);
    expect($message)->toContain('al menos 1 día de anticipación');
    
    // Far future date
    $farDate = Carbon::now()->addDays(31);
    $message = $service->getValidationMessage($farDate);
    expect($message)->toContain('hasta 30 días');
    
    // Sunday
    $sunday = Carbon::now()->next(Carbon::SUNDAY);
    $message = $service->getValidationMessage($sunday);
    expect($message)->toContain('domingos');
});

test('delivery time slot options are generated correctly', function () {
    $options = DeliveryTimeSlot::getOptions();
    
    expect($options)->toBeArray();
    expect($options)->toHaveCount(3);
    expect($options['morning'])->toBe('Mañana (9:00 - 12:00)');
    expect($options['afternoon'])->toBe('Tarde (14:00 - 17:00)');
    expect($options['evening'])->toBe('Noche (18:00 - 20:00)');
});

test('delivery status options are generated correctly', function () {
    $options = DeliveryStatus::getOptions();
    
    expect($options)->toBeArray();
    expect($options)->toHaveCount(4);
    expect($options['programado'])->toBe('Programado');
    expect($options['en_ruta'])->toBe('En Ruta');
    expect($options['entregado'])->toBe('Entregado');
    expect($options['reprogramado'])->toBe('Reprogramado');
});
