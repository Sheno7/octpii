<?php

namespace App\Filters\Markets\Vendors;

class SectorsFilter
{
    public function handle($query, \Closure $next)
    {

        $this->getSectors();
        if (count($this->getSectors()) > 0) {
            $query->whereHas('sectors', function ($q) {
                $q->whereIn('sectors.id', $this->getSectors());
            });
        }

        return $next($query);
    }

    public function getSectors()
    {
        if (request()->has('filter')) {
            $filter = request('filter');
            if (isset($filter['sectors']) && !empty($filter['sectors'])) {
                $sectors = explode(',', $filter['sectors']);
                return $sectors;
            }
        }
        return [];
    }
}
