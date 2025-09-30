<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'image_path',
        'product_type',
        'unit_code',
        'unit_description',
        'unit_price',
        'sale_price',
        'cost_price',
        'tax_type',
        'tax_rate',
        'current_stock',
        'minimum_stock',
        'track_inventory',
        'category_id',
        'brand_id',
        'category', // Mantener para compatibilidad
        'brand', // Mantener para compatibilidad
        'barcode',
        'status',
        'taxable',
        'for_sale',
        'featured',
        'created_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'cost_price' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'current_stock' => 'decimal:4',
        'minimum_stock' => 'decimal:4',
        'maximum_stock' => 'decimal:4',
        'weight' => 'decimal:3',
        'track_inventory' => 'boolean',
        'taxable' => 'boolean',
        'for_sale' => 'boolean',
        'for_purchase' => 'boolean',
        'featured' => 'boolean',
        'additional_attributes' => 'array',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoiceDetails(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForSale($query)
    {
        return $query->where('for_sale', true);
    }

    public function scopeProducts($query)
    {
        return $query->where('product_type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('product_type', 'service');
    }

    // Methods
    public function isService(): bool
    {
        return $this->product_type === 'service';
    }

    public function isProduct(): bool
    {
        return $this->product_type === 'product';
    }

    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->current_stock <= $this->minimum_stock;
    }

    public function getTaxAmount(float $amount): float
    {
        if (!$this->taxable || $this->tax_type === '20' || $this->tax_type === '30') {
            return 0;
        }
        
        return $amount * $this->tax_rate;
    }

    // Image methods
    public function getImageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        return \Storage::disk('public')->url($this->image_path);
    }

    public function hasImage(): bool
    {
        return !empty($this->image_path) && \Storage::disk('public')->exists($this->image_path);
    }

    public function deleteImage(): bool
    {
        if ($this->image_path && \Storage::disk('public')->exists($this->image_path)) {
            return \Storage::disk('public')->delete($this->image_path);
        }

        return true;
    }

    // Barcode methods
    public function generateUniqueBarcode(): string
    {
        do {
            // Generar código EAN-13 like (13 dígitos)
            $barcode = $this->generateBarcodeNumber();
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private function generateBarcodeNumber(): string
    {
        // Prefijo de empresa (3 dígitos) + ID del producto (6 dígitos) + checksum (4 dígitos)
        $prefix = str_pad($this->company_id, 3, '0', STR_PAD_LEFT);
        $productId = str_pad($this->id ?? rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $productId . $random;
    }

    public function ensureBarcodeExists(): void
    {
        if (empty($this->barcode)) {
            $this->barcode = $this->generateUniqueBarcode();
            $this->save();
        }
    }

    public function getBarcodeImageSvg(): string
    {
        if (!$this->barcode) {
            return '';
        }

        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
            return $generator->getBarcode($this->barcode, $generator::TYPE_CODE_128);
        } catch (\Exception $e) {
            return '<text>Error: ' . $e->getMessage() . '</text>';
        }
    }
}