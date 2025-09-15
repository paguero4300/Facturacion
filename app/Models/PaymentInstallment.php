<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstallment extends Model
{
    protected $fillable = [
        'invoice_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_amount',
        'pending_amount',
        'status',
        'paid_at',
        'payment_reference',
        'late_fee_rate',
        'late_fee_amount',
        'days_overdue',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'late_fee_rate' => 'decimal:4',
        'late_fee_amount' => 'decimal:2',
        'days_overdue' => 'integer',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', today());
    }

    // Methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date < today() && $this->status !== 'paid';
    }

    public function getDaysOverdue(): int
    {
        if ($this->isOverdue()) {
            return today()->diffInDays($this->due_date);
        }
        
        return 0;
    }

    public function calculateLateFee(): void
    {
        if ($this->isOverdue() && $this->late_fee_rate > 0) {
            $days = $this->getDaysOverdue();
            $this->days_overdue = $days;
            $this->late_fee_amount = $this->pending_amount * $this->late_fee_rate * $days;
            $this->save();
        }
    }

    public function markAsPaid(float $amount, string $reference = null): void
    {
        $this->paid_amount += $amount;
        $this->pending_amount = max(0, $this->amount - $this->paid_amount);
        
        if ($this->pending_amount == 0) {
            $this->status = 'paid';
            $this->paid_at = now();
        } else {
            $this->status = 'partial_paid';
        }
        
        if ($reference) {
            $this->payment_reference = $reference;
        }
        
        $this->save();
    }
}