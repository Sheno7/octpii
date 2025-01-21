<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model {
    use HasFactory, softDeletes;
    protected $table = 'booking';
    protected $fillable = [
        'customer_id',
        'date',
        'duration',
        'gender_prefrence',
        'is_favorite',
        'address_id',
        'area_id',
        'coupon_id',
        'status',
        'source',
        'total',
        'notes',
        'package_id',
        'branch_id',
    ];
    protected $casts = [
        'customer_id' => 'integer',
        'date' => 'date:Y-m-d H:i:s',
        'gender_prefrence' => 'integer',
        'is_favorite' => 'integer',
        'address_id' => 'integer',
        'area_id' => 'integer',
        'coupon_id' => 'integer',
        'status' => 'integer',
        'source' => 'integer',
        'total' => 'integer',
        'package_id' => 'integer',
        'branch_id' => 'integer',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    public function customer() {
        return $this->belongsTo(Customers::class);
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function area() {
        return $this->belongsTo(Areas::class);
    }

    public function bookingServices() {
        return $this->hasMany(BookingService::class);
    }

    public function services() {
        return $this->belongsToMany(VeServices::class, 'booking_service', 'booking_id', 'service_id');
    }
    public function maServices() {
        return $this->belongsToMany(Services::class, 'booking_service', 'booking_id', 'service_id');
    }
    public function bookingProvider() {
        return $this->hasMany(BookingProvider::class);
    }

    public function providers() {
        return $this->belongsToMany(Providers::class, 'booking_provider', 'booking_id', 'provider_id');
    }
    public function vendors() {
        return $this->belongsToMany(MaVendor::class, 'booking_vendor', 'booking_id', 'vendor_id');
    }
    /**
     * Get the package that owns the Booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get all of the transactions for the Booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function completed_transactions(): HasMany {
        return $this->hasMany(Transaction::class)->where('status', Status::PAYMENTCOMPLETED);
    }
}
