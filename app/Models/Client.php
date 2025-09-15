<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'document_type',
        'document_number',
        'business_name',
        'commercial_name',
        'address',
        'district',
        'province',
        'department',
        'ubigeo',
        'phone',
        'email',
        'contact_person',
        'credit_limit',
        'payment_days',
        'client_type',
        'status',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_days' => 'integer',
        'additional_data' => 'array',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDocumentType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    // Methods
    public function getFullDocumentAttribute(): string
    {
        return $this->document_type . '-' . $this->document_number;
    }

    public function isCompany(): bool
    {
        return $this->document_type === '6'; // RUC
    }

    public function isPerson(): bool
    {
        return in_array($this->document_type, ['1', '4', '7']); // DNI, CE, Pasaporte
    }
}