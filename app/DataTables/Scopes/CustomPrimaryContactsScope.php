<?php

namespace App\DataTables\Scopes;

class CustomPrimaryContactsScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $primaryContacts = array_get($this->request, 'search.primary_contacts');
        
        if ($primaryContacts) {
            $query->where(function ($q) {
                $q->where('family_position', 'Primary Contact')->orWhereNull('family_id');
            });
        }
        
        return $query;
    }
}
