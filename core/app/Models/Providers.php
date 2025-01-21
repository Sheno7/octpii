<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Providers extends Model {
    use HasFactory, softDeletes;
    protected $table = 'providers';
    protected $fillable = [
        'user_id',
        'address_id',
        'rank',
        'rating',
        'start_date',
        'resign_date',
        'salary',
        'commission_type',
        'commission_amount',
        'balance',
        'status',
        'branch_id',
    ];

    protected $casts = [
        //        'start_date' => 'date',
        //        'resign_date' => 'date',
        // format: Y-m-d
        'start_date' => 'datetime:Y-m-d',
        'resign_date' => 'datetime:Y-m-d',
        'rating' => 'double',
        'rank' => 'integer',
        'branch_id' => 'integer',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function areas() {
        return $this->belongsToMany(Areas::class, 'area_providers', 'provider_id', 'area_id');
    }

    public function services() {
        return $this->belongsToMany(VeServices::class, 'service_provider', 'provider_id', 'service_id');
    }

    public function avaliabilty() {
        return $this->hasMany(Avaliabilty::class);
    }

    public function address() {
        return $this->belongsTo(Address::class, 'owner_id');
    }

    /**
     * Get all of the workingSchedules for the Providers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workingSchedules(): HasMany {
        return $this->hasMany(WorkingSchedule::class, 'provider_id', 'id');
    }

    public function bookings() {
        return $this->belongsToMany(Booking::class, 'booking_provider', 'provider_id', 'booking_id');
    }


    //    public function ProviderAction()
    //    {
    //        return $this->hasMany(ProvidersAction::class,'provider_id', 'id');
    //    }
}
