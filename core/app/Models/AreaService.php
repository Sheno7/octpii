<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaService extends Model
{
    use HasFactory;

    protected $table = 'area_service';
    protected $fillable = [
        'area_id',
        'service_id',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Areas::class);
    }

    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}


