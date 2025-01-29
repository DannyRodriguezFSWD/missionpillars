<?php

namespace App\Traits\CustomFields;
use App\Models\CustomField;
use App\Models\CustomFieldValue;

/**
 *
 * @author josemiguel
 */
trait CustomFieldValuesTrait {
    
    public static function findOrCreate($value, $model, $field) {
        $customValue = CustomFieldValue::where([
            ['relation_id', '=', array_get($model, 'id')],
            ['relation_type', '=', array_get($model, 'id')],
            ['custom_field_id', '=', array_get($field, 'id')]
        ])->first();
        
        if(!$customValue){
            $customValue = new CustomFieldValue();
            array_set($customValue, 'custom_field_id', array_get($field, 'id'));
            array_set($customValue, 'relation_id', array_get($model, 'id'));
            array_set($customValue, 'relation_type', get_class($model));
            array_set($customValue, 'value', $value);
            auth()->user()->tenant->customFieldValues()->save($customValue);
        }
        
        return $customValue;
    }
    
    public static function createOrUpdate($value, $model, $field)
    {
        $customValue = CustomFieldValue::where([
            ['relation_id', '=', array_get($model, 'id')],
            ['relation_type', '=', get_class($model)],
            ['custom_field_id', '=', array_get($field, 'id')]
        ])->first();
        
        if ($customValue) {
            if (array_get($customValue, 'value') !== $value) {
                array_set($customValue, 'value', $value);
                $customValue->update();
            }
        } elseif ($value) {
            $customValue = new CustomFieldValue();
            array_set($customValue, 'custom_field_id', array_get($field, 'id'));
            array_set($customValue, 'relation_id', array_get($model, 'id'));
            array_set($customValue, 'relation_type', get_class($model));
            array_set($customValue, 'value', $value);
            auth()->user()->tenant->customFieldValues()->save($customValue);
        }
        
        return $customValue;
    }
}
