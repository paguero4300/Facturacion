<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'icon',
        'status',
        'web_order',
        'web_group',
        'show_on_web',
        'is_main_category',
        'main_category_id',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'web_order' => 'integer',
        'show_on_web' => 'boolean',
        'is_main_category' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relación con la categoría principal
    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'main_category_id');
    }

    // Relación con las subcategorías
    public function subCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'main_category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    
    public function scopeForWeb($query)
    {
        return $query->where('show_on_web', true);
    }
    
    public function scopeOrderByWeb($query)
    {
        return $query->orderBy('web_order', 'asc');
    }
    
    public function scopeInGroup($query, $group)
    {
        return $query->where('web_group', $group);
    }

    // Scopes para categorías principales y subcategorías
    public function scopeMainCategories($query)
    {
        return $query->where('is_main_category', true);
    }

    public function scopeSubCategories($query)
    {
        return $query->where('is_main_category', false)->whereNotNull('main_category_id');
    }

    public function scopeForMainCategory($query, $mainCategoryId)
    {
        return $query->where('main_category_id', $mainCategoryId);
    }

    // Methods
    public function getProductsCount(): int
    {
        return $this->products()->count();
    }
}