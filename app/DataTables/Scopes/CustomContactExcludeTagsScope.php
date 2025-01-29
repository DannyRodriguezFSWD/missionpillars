<?php

namespace App\DataTables\Scopes;

class CustomContactExcludeTagsScope extends MPScope
{
    public function apply($query)
    {
        $tag_ids = array_get($this->request, 'search.contact_excluded_tags');
        if (!$tag_ids) return $query;
        
        $query->whereDoesntHave('tags', function ($q) use ($tag_ids) {
            $q->whereIn('id', $tag_ids);
        });
        
        return $query;
    }
}
