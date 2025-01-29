<?php

namespace App\Classes;

use App\Models\Settings\Setting;
use App\Models\Settings\SettingValue;
/**
 * Description of Settings
 *
 * @author josemiguel
 */
class Settings {
    
    public static function get($key = null, $tenant_id = null, $args = []) {
        $settings = SettingValue::withoutGlobalScopes()
                        ->whereNotNull('tenant_id')
                        ->where([
                            ['tenant_id', '=', $tenant_id],
                            ['key', '=', $key],
                        ])
                        ->first();
                
        //if there are not user settings then get default settings
        if(is_null($settings)){
            $settings = SettingValue::withoutGlobalScopes()
                        ->whereNull('tenant_id')
                        ->where([
                            ['key', '=', $key],
                        ])
                        ->first();
        }
        
        return $settings;
    }
    
}
