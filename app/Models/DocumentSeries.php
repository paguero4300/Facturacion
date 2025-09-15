<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSeries extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'document_type',
        'series',
        'description',
        'current_number',
        'initial_number',
        'final_number',
        'is_default',
        'is_electronic',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_electronic' => 'boolean',
        'is_contingency' => 'boolean',
        'current_number' => 'integer',
        'initial_number' => 'integer',
        'final_number' => 'integer',
        'documents_issued' => 'integer',
        'last_used_at' => 'datetime',
        'validation_rules' => 'array',
        'additional_config' => 'array',
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

    public function scopeForDocumentType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function getNextNumber(): int
    {
        $this->increment('current_number');
        $this->touch('last_used_at');
        
        return $this->current_number;
    }

    public function getFullSeriesFormat(): string
    {
        return $this->series . '-' . str_pad($this->current_number, 8, '0', STR_PAD_LEFT);
    }
}