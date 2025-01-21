<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProvider extends Model
{
    use HasFactory;
    protected $table = 'vendor_providers';
    protected $fillable = [
        'vendor_id',
        'provider_id',
        'status'
    ];

    public function vendor()
    {
        return $this->belongsTo(MaVendor::class, 'vendor_id');
    }
}
