<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'package';
    protected $fillable = [
        'title',
        'customer_id',
        'provider_id',
        'status',
        'payment_status'
    ];

    public function customers() {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function providers() {
        return $this->belongsTo(Providers::class, 'provider_id');
    }

    public function packageServices() {
        return $this->hasMany(PackageServices::class);
    }

    public function services() {
        return $this->belongsToMany(VeServices::class, 'package_services', 'package_id', 'service_id')
            ->withPivot('status', 'price', 'duration');
    }

    public function booking() {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all of the transactions for the Package
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function completed_transactions(): HasMany {
        return $this->hasMany(Transaction::class)->where('status', Status::PAYMENTCOMPLETED);
    }
}
