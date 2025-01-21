<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFavorite extends Model
{
    use HasFactory;
    protected $table = 'customer_favorite';
    protected $fillable = [
        'customer_id',
        'provider_id'
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'provider_id' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
    public function provider()
    {
        return $this->belongsTo(Providers::class, 'provider_id');
    }
}



