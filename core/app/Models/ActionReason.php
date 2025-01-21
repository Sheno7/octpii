<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionReason extends Model {
    use HasFactory, softDeletes;
    protected $table = 'action_reason';
    protected $fillable = [
        'action',
        'reason',
        'booking_id',
        'status',
        'action_by',
    ];
    protected $casts = [
        'action' => 'integer',
        'reason' => 'string',
        'booking_id' => 'integer',
        'status' => 'integer',
    ];
}
