<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Settings\SettingValue;

class Setting extends BaseModel
{
    public function value() {
        return $this->hasOne(SettingValue::class)->whereNotNull('tenant_id');
    }
    
    public function values() {
        return $this->hasMany(SettingValue::class);
    }
}
