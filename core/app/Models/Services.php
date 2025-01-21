<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Services extends Model {
    use HasFactory;
    use SoftDeletes;
    protected $table = 'services';
    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'sector_id',
        'category_id',
        'status',
        'icon',
        'upid',
    ];

    protected $casts = [
        'title_ar' => 'string',
        'title_en' => 'string',
        'description_ar' => 'string',
        'description_en' => 'string',
        'sector_id' => 'integer',
        'category_id' => 'integer',
        'status' => 'integer',
        'icon' => 'string',
        'visible' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * Get the category that owns the Services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sectors(): BelongsTo {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }

    /**
     * Get the category that owns the Services
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }
}
