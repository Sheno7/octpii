<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OffDays extends Model {
    use HasFactory;
    protected $table = 'off_days';
    protected $fillable = [
        'from',
        'to',
        'provider_id', 'title'
    ];

    protected $casts = [
        'provider_id' => 'integer',
        'from' => 'date',
        'to' => 'date',
    ];
}
