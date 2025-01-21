<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingModelData extends Model
{
    use HasFactory;

    protected $table = 'pricing_model_data';
    protected $fillable = [
        'pricing_model_id',
        'price',
        'price_type',
        'price_type_id',
        'price_type_name',
        'price_type_description',
        ''];

    public function pricingModel()
    {
        return $this->belongsTo(PricingModels::class);
    }
}
