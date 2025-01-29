<?php

namespace App\DataTables\Scopes;

class CustomContactTypeScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $type = array_get($this->request, 'search.contact_type', 'all');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        return $query;
    }
}
