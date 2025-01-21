<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class CentralCity extends Model {
    use HasFactory, softDeletes, CentralConnection;

    protected $table = 'cities';

    protected $fillable = [
        'title_ar',
        'title_en',
        'country_id',
        'status',
        'upid',
    ];

    public function country() {
        return $this->belongsTo(CentralCountry::class, 'country_id');
    }

    public function areas() {
        return $this->hasMany(CentralArea::class, 'city_id');
    }
}
