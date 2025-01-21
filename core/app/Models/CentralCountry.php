<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class CentralCountry extends Model {
    use HasFactory, softDeletes, CentralConnection;
    protected $table = 'countries';
    protected $fillable = [
        'title_ar',
        'title_en',
        'code',
        'flag',
        'isocode',
        'currency',
        'created_at',
        'upid',
    ];

    public function cities() {
        return $this->hasMany(CentralCity::class, 'country_id');
    }
}
