<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'line_number',
        'product_code',
        'description',
        'unit_code',
        'unit_description',
        'quantity',
        'unit_price',
        'unit_value',
        'line_discount_amount',
        'gross_amount',
        'net_amount',
        'tax_type',
        'igv_rate',
        'igv_base_amount',
        'igv_amount',
        'total_taxes',
        'line_total',
        'is_free',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'unit_value' => 'decimal:4',
        'line_discount_percentage' => 'decimal:4',
        'line_discount_amount' => 'decimal:2',
        'line_charge_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'igv_rate' => 'decimal:4',
        'igv_base_amount' => 'decimal:2',
        'igv_amount' => 'decimal:2',
        'isc_rate' => 'decimal:6',
        'isc_base_amount' => 'decimal:2',
        'isc_amount' => 'decimal:2',
        'other_taxes_amount' => 'decimal:2',
        'total_taxes' => 'decimal:2',
        'line_total' => 'decimal:2',
        'is_free' => 'boolean',
        'expiry_date' => 'date',
        'additional_attributes' => 'array',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeByTaxType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeFreeItems($query)
    {
        return $query->where('is_free', true);
    }

    // Methods
    public function calculateAmounts(): void
    {
        // Calcular bruto
        $this->gross_amount = $this->quantity * $this->unit_price;
        
        // Aplicar descuento
        $this->net_amount = $this->gross_amount - $this->line_discount_amount + $this->line_charge_amount;
        
        // Calcular IGV
        if ($this->tax_type === '10') { // Gravado
            $this->igv_base_amount = $this->net_amount;
            $this->igv_amount = $this->net_amount * $this->igv_rate;
        } else {
            $this->igv_base_amount = 0;
            $this->igv_amount = 0;
        }
        
        // Total impuestos
        $this->total_taxes = $this->igv_amount + $this->isc_amount + $this->other_taxes_amount;
        
        // Total línea
        $this->line_total = $this->net_amount + $this->total_taxes;
    }

    public function isGravado(): bool
    {
        return in_array($this->tax_type, ['10', '11', '12', '13', '14', '15', '16', '17']);
    }

    public function isExonerado(): bool
    {
        return in_array($this->tax_type, ['20', '21']);
    }

    public function isInafecto(): bool
    {
        return in_array($this->tax_type, ['30', '31', '32', '33', '34', '35', '36', '37']);
    }
}