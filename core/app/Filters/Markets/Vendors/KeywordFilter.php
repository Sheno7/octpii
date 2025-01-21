<?php

namespace App\Filters\Markets\Vendors;

use Illuminate\Support\Facades\DB;

class KeywordFilter
{
    public function handle($query, \Closure $next)
    {
        if (request()->has('search')) {
            $query->where(function ($q) {
                return $q->where('org_name_ar', 'like', '%' . request('search') . '%')
                    ->orWhere(DB::raw('upper(org_name_en)'), 'like', '%' . strtoupper(request('search')) . '%');
            });
        }

        return $next($query);
    }
}
