<?php

namespace App\Models;

use App\Http\Resources\BranchResource;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model {

    protected $fillable = [
        'category_id',
        'name_en',
        'name_ar',
        'quantity',
        'minimum_quantity',
        'branch_id',
        'price',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    /**
     * Get the category that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
