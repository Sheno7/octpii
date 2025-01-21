<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sectors extends Model {
    use HasFactory, softDeletes;

    protected $table = 'sectors';
    protected $fillable = [
        'title_ar',
        'title_en',
        'status',
        'pricing_model_id',
        'icon',
        'multi_sessions',
        'customer_rating'
    ];

    protected $casts = [
        'title_ar' => 'string',
        'title_en' => 'string',
        'status' => 'integer',
        'pricing_model_id' => 'integer',
        'icon' => 'string',
        'multi_sessions' => 'bool',
        'customer_rating' => 'bool'
    ];

    public function services() {
        return $this->hasMany(Services::class, 'sector_id');
    }

    public function pricingModelSector() {
        return $this->hasMany(PricingModelSector::class, 'sector_id');
    }

    public function pricingModels() {
        return $this->hasMany(PricingModelSector::class, 'sector_id', 'id');
    }
    public function sectorAdditionalInformation() {
        return $this->hasMany(SectorAdditionalInformation::class, 'sector_id');
    }

    public function pricingModelsSector() {
        return $this->belongsToMany(PricingModels::class, 'pricing_model_sector', 'sector_id', 'pricing_model_id');
    }

    public function additionalInformation() {
        return $this->belongsToMany(
            AdditionalInformation::class,
            'sector_additional_information',
            'sector_id',
            'additional_information_id'
        )->withTimestamps();
    }

    /**
     * Get all of the categories for the Sectors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories(): HasMany {
        return $this->hasMany(ServiceCategory::class, 'sector_id');
    }
}
