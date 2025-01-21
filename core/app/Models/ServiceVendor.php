<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceVendor extends Model
{
    use HasFactory;
    protected $table = 'service_vendor';
    protected $fillable = [
        'vendor_id',
        'service_id',
        've_service_id',
        'status'
    ];

    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
    public function vendor()
    {
        return $this->belongsTo(MaVendor::class, 'vendor_id');
    }
}
