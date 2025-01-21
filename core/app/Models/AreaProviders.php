<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaProviders extends Model
{
    use HasFactory;
    protected $table = 'area_providers';
    protected $fillable = [
        'area_id',
        'provider_id',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Areas::class);
    }

    public function provider()
    {
        return $this->belongsTo(Providers::class);
    }
}
