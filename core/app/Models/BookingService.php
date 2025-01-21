<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingService extends Model
{
    use HasFactory , softDeletes;
    protected $table = 'booking_service';
    protected $fillable = [
        'booking_id',
        'service_id',
        'price',
        'total',
        'status',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'service_id' => 'integer',
        'price' => 'integer',
        'total' => 'integer',
        'duration' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Services::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vservice()
    {
        return $this->belongsTo(VeServices::class);
    }
}
