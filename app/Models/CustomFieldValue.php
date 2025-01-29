<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomFields\CustomFieldValuesTrait;

class CustomFieldValue extends BaseModel
{
    use CustomFieldValuesTrait;
    
    public function customField() {
        return $this->belongsTo(CustomField::class);
    }
}
