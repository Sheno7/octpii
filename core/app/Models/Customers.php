<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customers extends Model {
    use HasFactory, softDeletes;
    protected $table = 'customers';
    protected $fillable = [
        'user_id',
        'rating',
        'status',
        'wallet_balance',
        'branch_id',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the bookings for the Customers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings(): HasMany {
        return $this->hasMany(Booking::class, 'customer_id', 'id');
    }

    /**
     * Get all of the packages for the Customers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages(): HasMany {
        return $this->hasMany(Package::class, 'customer_id', 'id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'owner_id', 'id')->where('owner_type', 1);
    }
}
