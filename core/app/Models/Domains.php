<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domains extends BaseDomain
{
    use HasFactory;
    protected $table = 'domains';
    public $timestamps = true;
    public $incrementing = false;

    protected $fillable = [
        'domain',
        'tenant_id',
        'vendor_id',
        'market_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendor_id', 'id');
    }
    public function market()
    {
        return $this->belongsTo(Markets::class, 'market_id', 'id');
    }
}
