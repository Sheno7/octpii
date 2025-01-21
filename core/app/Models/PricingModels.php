<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingModels extends Model
{
    use HasFactory ,  softDeletes;
    protected $table = 'pricing_models';
    protected $fillable = [
        'name',
        'capacity',
        'variable_name',
        'pricing_type',
        'capacity_threshold',
        'additional_capacity',
        'markup',
        'base_price',
        'min_price',
        'upid',
    ];

    protected $casts = [
        'name' => 'string',
        'capacity' => 'boolean',
        'variable_name' => 'string',
        //'pricing_type' => 'enum:fixed,variable',
        'capacity_threshold' => 'boolean',
        'additional_capacity' => 'boolean',
        'base_price' => 'boolean',
        'min_price' => 'boolean',
        'markup' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function PricingModelSector()
    {
        return $this->hasMany(PricingModelSector::class, 'pricing_model_id');
    }


}
