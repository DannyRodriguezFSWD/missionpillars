<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomFields\CustomFieldsTrait;

class CustomField extends BaseModel
{
    use CustomFieldsTrait;
    
    public function customValues() {
        return $this->hasMany(CustomFieldValue::class);
    }
    
    public function scopeImported($query)
    {
        return $query->where('imported', true);
    }
    
    public function scopeNotImported($query)
    {
        return $query->where('imported', false);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderByRaw('-sort desc')->orderBy('id');
    }
    
    public function getOptionsArrayAttribute()
    {
        $array = $this->type === 'multiselect' ? [] : ['' => ''];
        
        if ($this->options) {
            $options = explode(',', $this->options);
            
            foreach ($options as $option) {
                $array[$option] = $option;
            }
        }
        
        return $array;
    }
}
