<?php

namespace App\Models;

use App\Enums\DeliveryTimeSlot;
use App\Enums\DeliveryStatus;
use App\Enums\PaymentValidationStatus;
use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'company_id',
        'client_id',
        'document_series_id',
        'series',
        'number',
        'full_number',
        'document_type',
        'issue_date',
        'issue_time',
        'due_date',
        'currency_code',
        'exchange_rate',
        'client_document_type',
        'client_document_number',
        'client_business_name',
        'client_address',
        'client_email',
        'operation_type',
        'subtotal',
        'igv_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'payment_phone',
        'payment_condition',
        'sunat_status',
        'status',
        'observations',
        'created_by',
        'updated_by',
        'paid_amount',
        'pending_amount',
        // Delivery fields
        'delivery_date',
        'delivery_time_slot',
        'delivery_notes',
        'delivery_status',
        'delivery_confirmed_at',
        // Payment validation fields
        'payment_evidence_path',
        'payment_validation_status',
        'payment_validated_at',
        'payment_validated_by',
        'payment_operation_number',
        'client_payment_phone',
        'payment_validation_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_exempt_amount' => 'decimal:2',
        'unaffected_amount' => 'decimal:2',
        'igv_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'credit_days' => 'integer',
        'sunat_sent_at' => 'datetime',
        'sunat_processed_at' => 'datetime',
        'additional_data' => 'array',
        // Delivery casts
        'delivery_date' => 'date',
        'delivery_time_slot' => DeliveryTimeSlot::class,
        'delivery_status' => DeliveryStatus::class,
        'delivery_confirmed_at' => 'datetime',
        // Payment validation casts
        'payment_validation_status' => PaymentValidationStatus::class,
        'payment_validated_at' => 'datetime',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documentSeries(): BelongsTo
    {
        return $this->belongsTo(DocumentSeries::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function paymentInstallments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function paymentValidatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_validated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('sunat_status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('sunat_status', 'accepted');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('issue_date', [$from, $to]);
    }

    // Methods
    public function getFullNumberAttribute(): string
    {
        return $this->series . '-' . $this->number;
    }

    public function isInvoice(): bool
    {
        return $this->document_type === '01';
    }

    public function isBoleta(): bool
    {
        return $this->document_type === '03';
    }

    public function isCreditNote(): bool
    {
        return $this->document_type === '07';
    }

    public function isNotaVenta(): bool
    {
        return $this->document_type === '09';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->details->sum('net_amount');
        $igv = $this->details->sum('igv_amount');
        
        $this->forceFill([
            'subtotal' => $subtotal,
            'igv_amount' => $igv,
            'total_amount' => $subtotal + $igv,
            'pending_amount' => $subtotal + $igv - $this->paid_amount,
        ])->saveQuietly();
    }

    // Delivery Methods
    public function hasDeliveryScheduled(): bool
    {
        return !is_null($this->delivery_date) && !is_null($this->delivery_time_slot);
    }

    public function isDeliveryOverdue(): bool
    {
        if (!$this->hasDeliveryScheduled()) {
            return false;
        }

        $deliveryDateTime = $this->getDeliveryDateTime();
        return $deliveryDateTime->isPast() && $this->delivery_status !== DeliveryStatus::ENTREGADO;
    }

    public function getDeliveryDateTime(): ?Carbon
    {
        if (!$this->delivery_date || !$this->delivery_time_slot) {
            return null;
        }

        $startTime = match($this->delivery_time_slot) {
            DeliveryTimeSlot::MORNING => '09:00',
            DeliveryTimeSlot::AFTERNOON => '14:00', 
            DeliveryTimeSlot::EVENING => '18:00',
        };

        return Carbon::parse($this->delivery_date->format('Y-m-d') . ' ' . $startTime);
    }

    public function getDeliveryTimeRangeAttribute(): ?string
    {
        return $this->delivery_time_slot?->timeRange();
    }

    public function canChangeDeliveryStatus(DeliveryStatus $newStatus): bool
    {
        if (!$this->delivery_status) {
            return true;
        }

        return $this->delivery_status->canTransitionTo($newStatus);
    }

    public function updateDeliveryStatus(DeliveryStatus $status, ?string $notes = null): bool
    {
        if (!$this->canChangeDeliveryStatus($status)) {
            return false;
        }

        $this->delivery_status = $status;
        
        if ($status === DeliveryStatus::ENTREGADO) {
            $this->delivery_confirmed_at = now();
        }

        if ($notes) {
            $this->delivery_notes = $notes;
        }

        return $this->save();
    }

    // Scopes for delivery
    public function scopeWithDeliveryScheduled($query)
    {
        return $query->whereNotNull('delivery_date')
                    ->whereNotNull('delivery_time_slot');
    }

    public function scopeByDeliveryDate($query, $date)
    {
        return $query->whereDate('delivery_date', $date);
    }

    public function scopeByDeliveryTimeSlot($query, DeliveryTimeSlot $timeSlot)
    {
        return $query->where('delivery_time_slot', $timeSlot);
    }

    public function scopeByDeliveryStatus($query, DeliveryStatus $status)
    {
        return $query->where('delivery_status', $status);
    }

    public function scopeDeliveryOverdue($query)
    {
        return $query->withDeliveryScheduled()
                    ->where('delivery_date', '<', now()->toDateString())
                    ->where('delivery_status', '!=', DeliveryStatus::ENTREGADO);
    }

    // Payment Validation Methods
    public function requiresPaymentValidation(): bool
    {
        $methodsRequiringValidation = ['yape', 'plin', 'transfer'];
        return in_array($this->payment_method, $methodsRequiringValidation);
    }

    public function hasPaymentEvidence(): bool
    {
        return !empty($this->payment_evidence_path) && 
               \Storage::exists($this->payment_evidence_path);
    }

    public function getPaymentEvidenceUrl(): ?string
    {
        if (!$this->hasPaymentEvidence()) {
            return null;
        }

        return \Storage::url($this->payment_evidence_path);
    }

    public function isPaymentPendingValidation(): bool
    {
        return $this->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION;
    }

    public function isPaymentApproved(): bool
    {
        return $this->payment_validation_status === PaymentValidationStatus::PAYMENT_APPROVED;
    }

    public function isPaymentRejected(): bool
    {
        return $this->payment_validation_status === PaymentValidationStatus::PAYMENT_REJECTED;
    }

    public function isCashOnDelivery(): bool
    {
        return $this->payment_method === 'cash' || 
               $this->payment_validation_status === PaymentValidationStatus::CASH_ON_DELIVERY;
    }

    public function approvePayment(?int $validatedBy = null, ?string $notes = null): bool
    {
        if (!$this->canChangePaymentStatus(PaymentValidationStatus::PAYMENT_APPROVED)) {
            return false;
        }

        $this->payment_validation_status = PaymentValidationStatus::PAYMENT_APPROVED;
        $this->payment_validated_at = now();
        $this->payment_validated_by = $validatedBy ?? \Auth::id();
        $this->status = 'paid'; // Cambiar estado de la factura
        
        if ($notes) {
            $this->payment_validation_notes = $notes;
        }

        $saved = $this->save();
        
        // Enviar email de aprobación
        if ($saved && $this->client_email) {
            try {
                Mail::to($this->client_email)->send(new PaymentApprovedMail($this));
            } catch (\Exception $e) {
                \Log::error('Error sending payment approved email', [
                    'invoice_id' => $this->id,
                    'email' => $this->client_email,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $saved;
    }

    public function rejectPayment(?int $validatedBy = null, ?string $notes = null): bool
    {
        if (!$this->canChangePaymentStatus(PaymentValidationStatus::PAYMENT_REJECTED)) {
            return false;
        }

        $this->payment_validation_status = PaymentValidationStatus::PAYMENT_REJECTED;
        $this->payment_validated_at = now();
        $this->payment_validated_by = $validatedBy ?? \Auth::id();
        
        if ($notes) {
            $this->payment_validation_notes = $notes;
        }

        $saved = $this->save();
        
        // Enviar email de rechazo
        if ($saved && $this->client_email) {
            try {
                Mail::to($this->client_email)->send(new PaymentRejectedMail($this, $notes ?? ''));
            } catch (\Exception $e) {
                \Log::error('Error sending payment rejected email', [
                    'invoice_id' => $this->id,
                    'email' => $this->client_email,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $saved;
    }

    public function canChangePaymentStatus(PaymentValidationStatus $newStatus): bool
    {
        if (!$this->payment_validation_status) {
            return true;
        }

        return $this->payment_validation_status->canTransitionTo($newStatus);
    }

    public function setPaymentValidationStatus(): void
    {
        if ($this->isCashOnDelivery()) {
            $this->payment_validation_status = PaymentValidationStatus::CASH_ON_DELIVERY;
        } elseif ($this->requiresPaymentValidation()) {
            $this->payment_validation_status = PaymentValidationStatus::PENDING_VALIDATION;
        } else {
            $this->payment_validation_status = PaymentValidationStatus::VALIDATION_NOT_REQUIRED;
        }
    }

    // Scopes for payment validation
    public function scopePendingPaymentValidation($query)
    {
        return $query->where('payment_validation_status', PaymentValidationStatus::PENDING_VALIDATION);
    }

    public function scopeApprovedPayments($query)
    {
        return $query->where('payment_validation_status', PaymentValidationStatus::PAYMENT_APPROVED);
    }

    public function scopeRejectedPayments($query)
    {
        return $query->where('payment_validation_status', PaymentValidationStatus::PAYMENT_REJECTED);
    }

    public function scopeByPaymentValidationStatus($query, PaymentValidationStatus $status)
    {
        return $query->where('payment_validation_status', $status);
    }
}
