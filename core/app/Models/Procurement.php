<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procurement extends Model {
    protected $fillable = [
        'date',
        'product_id',
        'quantity',
        'price',
        'notes',
        'branch_id',
        'transaction_id',
    ];

    protected $casts = [
        'date' => 'datetime',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'double',
        'branch_id' => 'integer',
        'transaction_id' => 'integer',
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

    /**
     * Get the transaction that owns the Procurement
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction(): BelongsTo {
        return $this->belongsTo(Transaction::class);
    }
}
