<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model {
    use HasFactory, softDeletes;
    protected $table = 'transactions';

    protected $fillable = [
        'booking_id',
        'provider_id',
        'payment_method_id',
        'amount',
        'date',
        'status',
        'note',
        'type',
        'package_id',
        'booking_id'
    ];
    protected $casts = [
        'amount' => 'double',
        'date' => 'datetime',
    ];


    public function provider() {
        return $this->belongsTo(Providers::class, 'provider_id', 'id');
    }
    public function booking() {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function package() {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    /**
     * Get the procurement associated with the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function procurement(): HasOne {
        return $this->hasOne(Procurement::class);
    }

    /**
     * Get the expense associated with the Transaction
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function expense(): HasOne {
        return $this->hasOne(Expense::class, 'transaction_id');
    }
}
