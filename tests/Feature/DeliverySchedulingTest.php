<?php

use App\Models\Invoice;
use App\Models\Company;
use App\Models\DocumentSeries;
use App\Models\User;
use App\Models\Product;
use App\Models\InvoiceDetail;
use App\Enums\DeliveryTimeSlot;
use App\Enums\DeliveryStatus;
use App\Services\DeliveryValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear datos básicos para las pruebas
    $this->company = Company::factory()->create();
    $this->series = DocumentSeries::factory()->create([
        'company_id' => $this->company->id,
        'series' => 'NV02',
        'current_number' => 1
    ]);
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
});

test('can create invoice with delivery scheduling', function () {
    $deliveryDate = Carbon::tomorrow();
    
    $invoice = Invoice::create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'series' => 'NV02',
        'number' => 1,
        'full_number' => 'NV02-00000001',
        'document_type' => '00',
        'issue_date' => now()->toDateString(),
        'issue_time' => now()->toTimeString(),
        'currency_code' => 'PEN',
        'client_document_type' => '1',
        'client_document_number' => '12345678',
        'client_business_name' => 'Test Client',
        'client_address' => 'Test Address',
        'subtotal' => 100.00,
        'total_amount' => 100.00,
        'payment_method' => 'cash',
        'status' => 'draft',
        'created_by' => $this->user->id,
        'delivery_date' => $deliveryDate,
        'delivery_time_slot' => DeliveryTimeSlot::MORNING,
        'delivery_notes' => 'Test delivery notes',
        'delivery_status' => DeliveryStatus::PROGRAMADO,
    ]);

    expect($invoice->hasDeliveryScheduled())->toBeTrue();
    expect($invoice->delivery_date)->toEqual($deliveryDate->toDateString());
    expect($invoice->delivery_time_slot)->toEqual(DeliveryTimeSlot::MORNING);
    expect($invoice->delivery_status)->toEqual(DeliveryStatus::PROGRAMADO);
});

test('can update delivery status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::tomorrow(),
        'delivery_time_slot' => DeliveryTimeSlot::AFTERNOON,
        'delivery_status' => DeliveryStatus::PROGRAMADO,
    ]);

    $result = $invoice->updateDeliveryStatus(DeliveryStatus::EN_RUTA);
    
    expect($result)->toBeTrue();
    expect($invoice->fresh()->delivery_status)->toEqual(DeliveryStatus::EN_RUTA);
});

test('cannot transition to invalid delivery status', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::tomorrow(),
        'delivery_time_slot' => DeliveryTimeSlot::AFTERNOON,
        'delivery_status' => DeliveryStatus::ENTREGADO,
    ]);

    $result = $invoice->updateDeliveryStatus(DeliveryStatus::EN_RUTA);
    
    expect($result)->toBeFalse();
    expect($invoice->fresh()->delivery_status)->toEqual(DeliveryStatus::ENTREGADO);
});

test('marks delivery confirmed when status changes to delivered', function () {
    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::tomorrow(),
        'delivery_time_slot' => DeliveryTimeSlot::AFTERNOON,
        'delivery_status' => DeliveryStatus::EN_RUTA,
    ]);

    $beforeTime = now();
    $invoice->updateDeliveryStatus(DeliveryStatus::ENTREGADO);
    $afterTime = now();
    
    $invoice->refresh();
    expect($invoice->delivery_status)->toEqual(DeliveryStatus::ENTREGADO);
    expect($invoice->delivery_confirmed_at)->toBeBetween($beforeTime, $afterTime);
});

test('delivery validation service validates dates correctly', function () {
    $service = new DeliveryValidationService();
    
    // Valid date (tomorrow)
    $validDate = Carbon::tomorrow();
    expect($service->isValidDeliveryDate($validDate))->toBeTrue();
    
    // Invalid date (yesterday)
    $pastDate = Carbon::yesterday();
    expect($service->isValidDeliveryDate($pastDate))->toBeFalse();
    
    // Invalid date (Sunday)
    $sunday = Carbon::now()->next(Carbon::SUNDAY);
    expect($service->isValidDeliveryDate($sunday))->toBeFalse();
    
    // Valid date (next Monday)
    $monday = Carbon::now()->next(Carbon::MONDAY);
    expect($service->isValidDeliveryDate($monday))->toBeTrue();
});

test('delivery time slots are available for correct days', function () {
    // Monday - all slots available
    $monday = Carbon::now()->next(Carbon::MONDAY);
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay($monday->format('l')))->toBeTrue();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay($monday->format('l')))->toBeTrue();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay($monday->format('l')))->toBeTrue();
    
    // Saturday - no evening slot
    $saturday = Carbon::now()->next(Carbon::SATURDAY);
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay($saturday->format('l')))->toBeTrue();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay($saturday->format('l')))->toBeTrue();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay($saturday->format('l')))->toBeFalse();
    
    // Sunday - no slots available
    $sunday = Carbon::now()->next(Carbon::SUNDAY);
    expect(DeliveryTimeSlot::MORNING->isAvailableOnDay($sunday->format('l')))->toBeFalse();
    expect(DeliveryTimeSlot::AFTERNOON->isAvailableOnDay($sunday->format('l')))->toBeFalse();
    expect(DeliveryTimeSlot::EVENING->isAvailableOnDay($sunday->format('l')))->toBeFalse();
});

test('can filter invoices by delivery date', function () {
    $tomorrow = Carbon::tomorrow();
    $dayAfter = Carbon::tomorrow()->addDay();
    
    $invoice1 = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => $tomorrow,
        'delivery_time_slot' => DeliveryTimeSlot::MORNING,
    ]);
    
    $invoice2 = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => $dayAfter,
        'delivery_time_slot' => DeliveryTimeSlot::AFTERNOON,
    ]);
    
    $invoice3 = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        // No delivery date
    ]);
    
    $tomorrowDeliveries = Invoice::byDeliveryDate($tomorrow)->get();
    expect($tomorrowDeliveries)->toHaveCount(1);
    expect($tomorrowDeliveries->first()->id)->toEqual($invoice1->id);
});

test('can filter invoices with delivery scheduled', function () {
    $invoiceWithDelivery = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::tomorrow(),
        'delivery_time_slot' => DeliveryTimeSlot::MORNING,
    ]);
    
    $invoiceWithoutDelivery = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        // No delivery scheduled
    ]);
    
    $scheduledDeliveries = Invoice::withDeliveryScheduled()->get();
    expect($scheduledDeliveries)->toHaveCount(1);
    expect($scheduledDeliveries->first()->id)->toEqual($invoiceWithDelivery->id);
});

test('can identify overdue deliveries', function () {
    $overdueInvoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::yesterday(),
        'delivery_time_slot' => DeliveryTimeSlot::MORNING,
        'delivery_status' => DeliveryStatus::PROGRAMADO,
    ]);
    
    $upcomingInvoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::tomorrow(),
        'delivery_time_slot' => DeliveryTimeSlot::MORNING,
        'delivery_status' => DeliveryStatus::PROGRAMADO,
    ]);
    
    $deliveredInvoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'document_series_id' => $this->series->id,
        'created_by' => $this->user->id,
        'delivery_date' => Carbon::yesterday(),
        'delivery_time_slot' => DeliveryTimeSlot::AFTERNOON,
        'delivery_status' => DeliveryStatus::ENTREGADO,
    ]);
    
    expect($overdueInvoice->isDeliveryOverdue())->toBeTrue();
    expect($upcomingInvoice->isDeliveryOverdue())->toBeFalse();
    expect($deliveredInvoice->isDeliveryOverdue())->toBeFalse();
    
    $overdueDeliveries = Invoice::deliveryOverdue()->get();
    expect($overdueDeliveries)->toHaveCount(1);
    expect($overdueDeliveries->first()->id)->toEqual($overdueInvoice->id);
});

test('checkout process validates delivery information', function () {
    $this->artisan('route:clear');
    
    // Add product to cart
    session(['cart' => [
        1 => [
            'id' => $this->product->id,
            'name' => $this->product->name,
            'price' => 50.00,
            'quantity' => 2,
            'image' => null
        ]
    ]]);
    
    // Test with valid delivery data
    $validData = [
        'client_name' => 'Test Customer',
        'client_phone' => '987654321',
        'client_email' => 'test@example.com',
        'client_address' => 'Test Address 123',
        'payment_method' => 'cash',
        'delivery_date' => Carbon::tomorrow()->format('Y-m-d'),
        'delivery_time_slot' => 'morning',
        'delivery_notes' => 'Please ring doorbell'
    ];
    
    $response = $this->post(route('checkout.process'), $validData);
    $response->assertRedirect();
    
    $invoice = Invoice::where('client_business_name', 'Test Customer')->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->hasDeliveryScheduled())->toBeTrue();
    expect($invoice->delivery_time_slot)->toEqual(DeliveryTimeSlot::MORNING);
    expect($invoice->delivery_status)->toEqual(DeliveryStatus::PROGRAMADO);
});

test('checkout process rejects invalid delivery dates', function () {
    $this->artisan('route:clear');
    
    // Add product to cart
    session(['cart' => [
        1 => [
            'id' => $this->product->id,
            'name' => $this->product->name,
            'price' => 50.00,
            'quantity' => 2,
            'image' => null
        ]
    ]]);
    
    // Test with Sunday delivery date (should be rejected)
    $invalidData = [
        'client_name' => 'Test Customer',
        'client_phone' => '987654321',
        'client_address' => 'Test Address 123',
        'payment_method' => 'cash',
        'delivery_date' => Carbon::now()->next(Carbon::SUNDAY)->format('Y-m-d'),
        'delivery_time_slot' => 'morning',
    ];
    
    $response = $this->post(route('checkout.process'), $invalidData);
    $response->assertSessionHasErrors('delivery_date');
});
