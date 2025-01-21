<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VeServices extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 've_services';
    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'duration',
        'base_price',
        'pricing_model_id',
        'capacity',
        'capacity_threshold',
        'status',
        'icon',
        'cost',
        'service_location',
        'markup',
        'visible',
        'upid',
        'category_id',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'markup' => 'integer',
        'duration' => 'double',
        'service_location' => 'integer',
    ];

    public function pricingModel() {
        return $this->belongsTo(PricingModels::class);
    }

    public function pricingModelData() {
        return $this->belongsTo(PricingModelData::class, 'pricing_model_id')->with('pricingModelData');
    }

    public function AreaService() {
        return $this->hasMany(AreaService::class, 'service_id')->with('area');
    }

    public function areas() {
        return $this->belongsToMany(Areas::class, 'area_service', 'service_id', 'area_id');
    }

    public function packageServices() {
        return $this->hasMany(PackageServices::class, 'service_id');
    }

    /**
     * Get the category that owns the Services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sectors(): BelongsTo {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }

    /**
     * The providers that belong to the VeServices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function providers(): BelongsToMany {
        return $this->belongsToMany(Providers::class, 'service_provider', 'service_id', 'provider_id');
    }

    /**
     * The bookings that belong to the VeServices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bookings(): BelongsToMany {
        return $this->belongsToMany(Booking::class, 'booking_service', 'service_id', 'booking_id');
    }

    /**
     * Get the category that owns the VeServices
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }
}
