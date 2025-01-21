<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model {
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'payment_method_id',
        'branch_id',
        'category_id',
        'sector_id',
        'amount',
        'created_by',
        'date',
        'attachment',
        'notes',
    ];

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'sector_id' => 'integer',
        'created_by' => 'integer',
        'date' => 'datetime',
        'notes' => 'array',
    ];

    protected static function booted() {
        static::addGlobalScope(new BranchScope);
    }

    /**
     * Get the category that owns the Expense
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Get the sector that owns the Expense
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector(): BelongsTo {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }

    /**
     * Get the branch that owns the Expense
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch(): BelongsTo {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the transaction that relates to the Expense
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction(): BelongsTo {
        return $this->belongsTo(Transaction::class);
    }
}
