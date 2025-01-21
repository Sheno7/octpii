<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model {
    use HasFactory, softDeletes;
    protected $table = 'address';
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'city',
        'state',
        'zip',
        'country',
        'owner_id',
        'owner_type',
        'location_name',
        'unit_type',
        'unit_size',
        'street_name',
        'building_number',
        'floor_number',
        'unit_number',
        'notes',
        'area_id',
    ];

    //    public function areas()
    //    {
    //        return $this->belongsToMany(Areas::class, 'area_address', 'address_id', 'area_id');
    //    }

    public function provider() {
        return $this->belongsTo(Providers::class, 'owner_id');
    }

    public function area() {
        return $this->belongsTo(Areas::class);
    }
}
