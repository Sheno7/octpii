<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingProvider extends Model
{
    use HasFactory , softDeletes;
    protected $table = 'booking_provider';
    protected $fillable = [
        'booking_id',
        'provider_id',
        'commission_type',
        'commission_amount',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'provider_id' => 'integer',
        'commission_type' => 'integer',
        'commission_amount' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function providers()
    {
        return $this->belongsTo(Providers::class);
    }

}
