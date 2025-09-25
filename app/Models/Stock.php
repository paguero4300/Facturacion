<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'warehouse_id',
        'qty',
        'min_qty',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'min_qty' => 'decimal:4',
    ];

    // Laravel no soporta claves primarias compuestas nativamente
    // Usamos id auto-incremental por defecto
    public $incrementing = true;

    // Relaciones
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('qty', '<=', 'min_qty')
                    ->whereNotNull('min_qty');
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Accessors
    public function getIsLowStockAttribute(): bool
    {
        return $this->min_qty !== null && $this->qty <= $this->min_qty;
    }

    // Methods
    public function isLowStock(): bool
    {
        return $this->min_qty !== null && $this->qty <= $this->min_qty;
    }
}
