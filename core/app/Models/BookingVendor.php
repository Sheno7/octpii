<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingVendor extends Model
{
    use HasFactory , softDeletes;
    protected $table = 'booking_vendor';
    protected $fillable = [
        'booking_id',
        'vendor_id',
        'commission_type',
        'commission_amount',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'vendor_id' => 'integer',
        'commission_type' => 'integer',
        'commission_amount' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vendors()
    {
        return $this->belongsTo(Vendors::class);
    }

}
