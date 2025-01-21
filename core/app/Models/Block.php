<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $table = 'block';
    protected $fillable = [
        'customer_id',
        'provider_id',
        'action_by'
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Providers::class, 'provider_id', 'id');
    }

    protected $casts = [
        'customer_id' => 'integer',
        'provider_id' => 'integer',
        'action_by' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}


