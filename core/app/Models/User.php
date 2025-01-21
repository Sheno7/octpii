<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'phone',
        'country_id',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function Domain() {
        return $this->belongsTo(Domains::class);
    }

    /**
     * Get the provider associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function provider(): HasOne {
        return $this->hasOne(Providers::class);
    }

    /**
     * Get the customer associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer(): HasOne {
        return $this->hasOne(Customers::class);
    }

    /**
     * Get the vendor associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vendor(): HasOne {
        return $this->hasOne(Vendors::class);
    }

    /**
     * Get the market associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function market(): HasOne {
        return $this->hasOne(Markets::class);
    }


    /**
     * Get the country that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country(): BelongsTo {
        return $this->belongsTo(Countries::class, 'country_id', 'id');
    }

    /**
     * Get all of the fcmTokens for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fcmTokens(): HasMany {
        return $this->hasMany(FcmToken::class);
    }

    /**
     * Get all of the mobileNotifications for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mobileNotifications(): HasMany {
        return $this->hasMany(MobileNotification::class);
    }

    /**
     * The favoriteVendors that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteVendors(): BelongsToMany {
        return $this->belongsToMany(MaVendor::class, 'user_vendor', 'user_id', 'vendor_id');
    }
}
