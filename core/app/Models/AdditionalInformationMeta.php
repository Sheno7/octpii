<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalInformationMeta extends Model
{
    use HasFactory;
    protected $table = 'additional_information_metadata';
    protected $fillable = ['additional_info_id', 'customer_id', 'key', 'value'];
    protected $casts = ['additional_info_id' => 'integer', 'customer_id' => 'integer', 'key' => 'string', 'value' => 'json'];
}
