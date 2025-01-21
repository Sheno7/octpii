<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $hidden = ['relatedModel'];
    protected $table = 'otp';
    protected $fillable = [
        'otp',
        'phone',
        'country_id'
    ];

}
