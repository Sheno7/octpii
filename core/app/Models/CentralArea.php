<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class CentralArea extends Model {
    use HasFactory, softDeletes, CentralConnection;
    protected $table = 'areas';
    protected $fillable = [
        'title_ar',
        'title_en',
        'lat',
        'long',
        'city_id',
        'status',
    ];

    public function city() {
        return $this->belongsTo(CentralCity::class, 'city_id');
    }

    // public function providers() {
    //     return $this->belongsToMany(Providers::class, 'area_providers', 'area_id', 'provider_id');
    // }

    // public function avaliabilty() {
    //     return $this->hasMany(Avaliabilty::class);
    // }

    // public function address() {
    //     return $this->hasMany(Address::class);
    // }
}
