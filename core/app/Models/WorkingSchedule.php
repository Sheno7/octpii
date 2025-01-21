<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\DayTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingSchedule extends Model {
    use HasFactory, DayTrait;
    protected $table = 'working_schedule';
    protected $fillable = [
        'day',
        'from',
        'to',
        'provider_id',
        'branch_id',
    ];

    protected $casts = [
        'from' => 'double',
        'to' => 'double',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    public function provider() {
        return $this->belongsTo(Providers::class, 'provider_id');
    }
}
