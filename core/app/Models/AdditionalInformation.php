<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalInformation extends Model {
    use HasFactory;
    protected $table = 'additional_information';
    protected $fillable = [
        'customer_id',
        'type',
        'hasfile',
        'value',
    ];
    protected $casts = [
        'customer_id' => 'integer',
        'type' => 'string',
        'hasfile' => 'boolean',
        'value' => 'json',
    ];
}
