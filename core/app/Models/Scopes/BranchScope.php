<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope {
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void {
        // Retrieve the current branch ID from the request
        $branchId = request()->input('selected_branch');

        // Apply the branch filter to the query
        if ($branchId) {
            $builder->where('branch_id', $branchId);
        }
    }
}
