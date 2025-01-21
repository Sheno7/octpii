<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model {
    protected $fillable = [
        'text_en',
        'text_ar',
        'role',
    ];
}
