<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Countries extends Model
{
    use HasFactory , softDeletes;
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

    public function cities()
    {
        return $this->hasMany(Cities::class, 'country_id');
    }
}
