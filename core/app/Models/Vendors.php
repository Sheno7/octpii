<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Vendors extends Model {
    use HasFactory, softDeletes, HasSubscriptions,CentralConnection;
    protected $table = 'vendors';
    protected $fillable = [
        'org_name_ar',
        'org_name_en',
        'sector_id',
        'status',
        'user_id',
        'services_count'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'org_name_ar' => 'string',
        'org_name_en' => 'string',
        'sector_id' => 'integer',
        'status' => 'integer',
        'user_id' => 'integer',
        'services_count' => 'integer'
    ];

    /**
     * The sectors that belong to the Vendors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sectors(): BelongsToMany {
        return $this->belongsToMany(Sectors::class, 'sector_vendor', 'vendor_id', 'sector_id')->withTimestamps();
    }

    public function settings() {
        return $this->hasOne(Setting::class, 'vendor_id', 'id');
    }

    /**
     * Get the domain associated with the Vendors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function domain(): HasOne {
        return $this->hasOne(Domains::class, 'vendor_id');
    }
}
