<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaProvider extends Model {
    use HasFactory, softDeletes;

    protected $table = 'vendor_providers';
    protected $fillable = [
        'vendor_id',
        'provider_id',
        'status',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'vendor_id' => 'integer',
        'provider_id' => 'integer',
    ];
}
