<?php

namespace App\Traits\CustomFields;
use App\Models\CustomField;
use App\Models\CustomFieldValue;

/**
 *
 * @author josemiguel
 */
trait CustomFieldsTrait {
    
    public static function findOrCreate($name, $model, $type = 'text') {
        $field = CustomField::where('name', $name)->first();
        if(!$field){
            $field = new CustomField();
            array_set($field, 'name', $name);
            array_set($field, 'type', $type);
            array_set($field, 'model', get_class($model));
            
            auth()->user()->tenant->customFields()->save($field);
        }
        return $field;
    }
    
}
