<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;

class MaVendor extends Model {
    use HasFactory, softDeletes, HasSubscriptions;
    protected $table = 'vendors';
    protected $fillable = [
        'tenant_id',
        'vendor_up_id',
        'status',
        'commission_type',
        'commission_amount',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'vendor_up_id' => 'integer',
        'status' => 'integer',
        'commission_type' => 'integer',
        'commission_amount' => 'float',
    ];

    /**
     * The sectors that belong to the Vendors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sectors(): BelongsToMany {
        return $this->belongsToMany(Sectors::class, 'sector_vendor', 'vendor_id', 'sector_id')->withTimestamps();
    }
    public function services(): BelongsToMany {
        return $this->belongsToMany(Services::class, 'service_vendor', 'vendor_id', 'service_id')
            ->withPivot(['ve_service_id', 'status'])
            ->withTimestamps();
    }
    /**
     * Get all of the providers for the MaVendor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function providers(): HasMany {
        return $this->hasMany(MaProvider::class, 'vendor_id', 'id');
    }
    public function settings() {
        return $this->hasOne(Setting::class, 'vendor_id', 'id');
    }
    public function upVendor() {
        return $this->belongsTo(Vendors::class, 'vendor_up_id', 'id');
    }

    /**
     * The users that belong to the MaVendor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'user_vendor', 'vendor_id', 'user_id');
    }
}
