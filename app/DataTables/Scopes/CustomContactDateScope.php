<?php

namespace App\DataTables\Scopes;

class CustomContactDateScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $createdAtStart = localizeDate(array_get($this->request, 'search.created_at_from'), 'start');
        $createdAtEnd = localizeDate(array_get($this->request, 'search.created_at_to'), 'end');
        
        if ($createdAtStart) {
            $query->where('created_at','>=', $createdAtStart);
        }
        if ($createdAtEnd) {
            $query->where('created_at','<=', $createdAtEnd);
        }
        
        $updatedAtStart = localizeDate(array_get($this->request, 'search.updated_at_from'), 'start');
        $updatedAtEnd = localizeDate(array_get($this->request, 'search.updated_at_to'), 'end');
        
        if ($updatedAtStart) {
            $query->where('updated_at','>=', $updatedAtStart);
        }
        if ($updatedAtEnd) {
            $query->where('updated_at','<=', $updatedAtEnd);
        }
        
        return $query;
    }
}
