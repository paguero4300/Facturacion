<?php

namespace App\Models;

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
}
