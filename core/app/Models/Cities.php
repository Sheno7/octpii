<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cities extends Model
{
    use HasFactory, softDeletes;

    protected $table = 'cities';

    protected $fillable = [
        'title_ar',
        'title_en',
        'country_id',
        'status',
        'upid',
    ];

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    public function areas()
    {
        return $this->hasMany(Areas::class, 'city_id');
    }
}





