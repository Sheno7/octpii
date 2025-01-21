<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, softDeletes;
    protected $table = 'media';
    protected $fillable = [
        'title',
       // 'model_type',
        'model_id',
        'file'
    ];
    protected $casts = [
        'title' => 'string',
       // 'model_type' => 'string',
        'model_id' => 'integer',
        'file' => 'string',
    ];
}
