<?php

namespace App\Filters\Markets\Vendors;

class RatingFilter
{
    public function handle($query, \Closure $next)
    {
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        return $next($query);
    }
}
