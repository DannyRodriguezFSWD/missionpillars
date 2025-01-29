<?php

namespace App\DataTables\Scopes;

class CustomContactTagsScope extends MPScope
{
    public function apply($query)
    {
        $tag_ids = array_get($this->request, 'search.contact_tags');
        if (!$tag_ids) return $query;
        
        $query->whereHas('tags', function ($q) use ($tag_ids) {
            $q->whereIn('id', $tag_ids);
        });
        
        return $query;
    }
}
