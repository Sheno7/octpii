<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliabilty extends Model {
    use HasFactory;
    protected $table = 'avaliabilty';
    protected $fillable = [
        'provider_id',
        'date',
        'from',
        'to',
    ];

    public function providers() {
        return $this->belongsTo(Providers::class);
    }

    public function off_days() {
        return $this->hasMany(OffDays::class);
    }

    //    public function area()
    //    {
    //        return $this->belongsTo(Areas::class);
    //    }

    //cast
    protected $casts = [
        'date' => 'date',
        'from' => 'float',
        'to' => 'float',
    ];
}
