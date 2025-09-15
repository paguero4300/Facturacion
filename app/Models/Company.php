<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ruc',
        'business_name',
        'commercial_name',
        'address',
        'district',
        'province',
        'department',
        'ubigeo',
        'phone',
        'email',
        'tax_regime',
        'ose_provider',
        'ose_endpoint',
        'ose_username',
        'ose_password',
        'qpse_config_token',
        'qpse_access_token',
        'qpse_token_expires_at',
        'qpse_last_response',
        'status',
        'sunat_production',
        'factiliza_token',
    ];

    protected $casts = [
        'sunat_production' => 'boolean',
        'additional_config' => 'array',
        'qpse_last_response' => 'array',
        'qpse_token_expires_at' => 'datetime',
    ];

    // Relationships
    public function documentSeries(): HasMany
    {
        return $this->hasMany(DocumentSeries::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    // QPse Token Management
    public function hasValidQpseToken(): bool
    {
        return $this->qpse_access_token && 
               $this->qpse_token_expires_at && 
               $this->qpse_token_expires_at->isFuture();
    }

    public function hasQpseConfigToken(): bool
    {
        return !empty($this->qpse_config_token);
    }

    public function hasQpseCredentials(): bool
    {
        return !empty($this->ose_username) && !empty($this->ose_password);
    }

    public function isQpseConfigured(): bool
    {
        return $this->ose_provider === 'qpse' && 
               $this->hasQpseCredentials() &&
               !empty($this->ose_endpoint);
    }

    public function getQpseTokenExpirationStatus(): string
    {
        if (!$this->qpse_access_token) {
            return 'no_token';
        }
        
        if (!$this->qpse_token_expires_at) {
            return 'unknown_expiration';
        }
        
        if ($this->qpse_token_expires_at->isPast()) {
            return 'expired';
        }
        
        if ($this->qpse_token_expires_at->diffInHours(null, false) < 24) {
            return 'expires_soon';
        }
        
        return 'valid';
    }
}