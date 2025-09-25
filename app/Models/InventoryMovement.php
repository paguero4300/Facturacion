<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    const TYPE_OPENING = 'OPENING';
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const TYPE_TRANSFER = 'TRANSFER';
    const TYPE_ADJUST = 'ADJUST';

    protected $fillable = [
        'company_id',
        'product_id',
        'type',
        'from_warehouse_id',
        'to_warehouse_id',
        'qty',
        'reason',
        'ref_type',
        'ref_id',
        'user_id',
        'idempotency_key',
        'movement_date',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'movement_date' => 'datetime',
    ];

    // Relaciones
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where(function ($q) use ($warehouseId) {
            $q->where('from_warehouse_id', $warehouseId)
              ->orWhere('to_warehouse_id', $warehouseId);
        });
    }

    // Métodos de utilidad
    public static function getTypes(): array
    {
        return [
            self::TYPE_OPENING => 'Apertura',
            self::TYPE_IN => 'Entrada',
            self::TYPE_OUT => 'Salida',
            self::TYPE_TRANSFER => 'Transferencia',
            self::TYPE_ADJUST => 'Ajuste',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getWarehouseMovementDescription(): string
    {
        switch ($this->type) {
            case self::TYPE_OPENING:
            case self::TYPE_IN:
                return '→ ' . ($this->toWarehouse->name ?? 'N/A');
            case self::TYPE_OUT:
                return ($this->fromWarehouse->name ?? 'N/A') . ' →';
            case self::TYPE_TRANSFER:
                return ($this->fromWarehouse->name ?? 'N/A') . ' → ' . ($this->toWarehouse->name ?? 'N/A');
            case self::TYPE_ADJUST:
                $warehouse = $this->fromWarehouse ?? $this->toWarehouse;
                return $warehouse ? $warehouse->name : 'N/A';
            default:
                return 'N/A';
        }
    }
}
