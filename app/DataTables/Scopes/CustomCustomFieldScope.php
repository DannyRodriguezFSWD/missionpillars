<?php

namespace App\DataTables\Scopes;

class CustomCustomFieldScope extends MPScope
{
    public function apply($query)
    {
        $customFieldId = array_get($this->request, 'search.custom_field');
        $value = array_get($this->request, 'search.custom_field_value');
        
        if (!$customFieldId || !$value) {
            return $query;
        }
        
        $query->whereHas('customFieldValues', function ($q) use ($customFieldId, $value) {
            $q->where('custom_field_id', $customFieldId)->where('value', 'like', '%'.$value.'%');
        });
        
        return $query;
    }
}
