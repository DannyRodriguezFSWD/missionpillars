<?php

namespace App\DataTables\Scopes;

class CustomGroupScope extends MPScope
{
    public function apply($query)
    {
        $group_ids = array_get($this->request, 'search.groups');
        
        if (!$group_ids) return $query;
        
        $query->whereHas('groups', function ($q) use ($group_ids) {
            $q->whereIn('group_id', $group_ids);
        });
        
        return $query;
    }
}
