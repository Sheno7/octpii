<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adjustment extends Model {
    protected $fillable = [
        'branch_id',
        'date',
        'product_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'date' => 'datetime',
        'product_id' => 'integer',
        'quantity' => 'integer',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    /**
     * Get the product that owns the Procurement
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
