<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingModelSector extends Model
{
    use HasFactory;
    protected $table = 'pricing_model_sector';
    protected $fillable = [
        'pricing_model_id',
        'sector_id',
    ];
    protected $casts = [
        'pricing_model_id' => 'integer',
        'sector_id' => 'integer',
    ];

    public function pricingModels()
    {
        return $this->belongsToMany(PricingModels::class, 'pricing_model_sectors', 'sector_id', 'pricing_model_id');
    }
}
