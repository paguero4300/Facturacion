<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebConfiguration extends Model
{
    protected $table = 'web_configuration';
    
    protected $fillable = [
        'company_id',
        'telefono_huancayo',
        'telefono_lima',
        'email',
        'horario_atencion',
        'tiktok',
        'instagram',
        'facebook',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}