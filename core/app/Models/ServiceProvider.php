<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProvider extends Model
{
    use HasFactory;
    protected $table = 'service_provider';
    protected $fillable = [
        'provider_id',
        'service_id',
        'status'
    ];

    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
    public function provider()
    {
        return $this->belongsTo(Providers::class, 'provider_id');
    }
}
