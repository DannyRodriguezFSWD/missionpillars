<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldSection extends BaseModel
{
    public function customFields()
    {
        return $this->hasMany(CustomField::class);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('tenant_id')->orderByRaw('-sort desc')->orderBy('id');
    }
}
