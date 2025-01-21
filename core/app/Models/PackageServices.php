<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageServices extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'package_services';
    protected $fillable = ['package_id', 'service_id', 'price', 'duration', 'status'];

    public function service() {
        return $this->belongsTo(VeServices::class, 'service_id');
    }

    public function package() {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
