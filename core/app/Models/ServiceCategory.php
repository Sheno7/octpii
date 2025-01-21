<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model {
    use HasFactory;
    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'sector_id',
        'icon',
        'status',
    ];

    /**
     * Get the sector that owns the ServiceCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector(): BelongsTo {
        return $this->belongsTo(Sectors::class);
    }

    /**
     * Get all of the services for the ServiceCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services(): HasMany {
        return $this->hasMany(Services::class, 'category_id', 'id');
    }
}
