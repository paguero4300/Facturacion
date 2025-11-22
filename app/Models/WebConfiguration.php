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
        'banner_1_imagen',
        'banner_1_type',
        'banner_1_video',
        'banner_1_titulo',
        'banner_1_texto',
        'banner_1_link',
        'banner_2_imagen',
        'banner_2_type',
        'banner_2_video',
        'banner_2_titulo',
        'banner_2_texto',
        'banner_2_link',
        'banner_3_imagen',
        'banner_3_type',
        'banner_3_video',
        'banner_3_titulo',
        'banner_3_texto',
        'banner_3_link',
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