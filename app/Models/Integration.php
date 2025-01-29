<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends BaseModel
{
    protected $table = 'integrations';
    
    public function values() 
    {
        return $this->hasMany(IntegrationValue::class);
    }
    
    public function getServiceShortAttribute()
    {
        if (strtolower($this->service) === 'continue to give') {
            return 'c2g';
        } else {
            return strtolower($this->service);
        }
    }
    
    public function getUrlAttribute()
    {
        $value = $this->values()->where('key', 'url')->first();
        return array_get($value, 'value');
    }
    
    public function getUsernameAttribute()
    {
        $value = $this->values()->where('key', 'username')->first();
        return array_get($value, 'value');
    }
    
    public function getPasswordAttribute()
    {
        $value = $this->values()->where('key', 'password')->first();
        return array_get($value, 'value');
    }
    
    public function getCustomFieldsAttribute()
    {
        $value = $this->values()->where('key', 'custom_fields')->first();
        return json_decode(array_get($value, 'value'), true);
    }
    
    public function getApiKeyAttribute()
    {
        $value = $this->values()->where('key', 'API_KEY')->first();
        return array_get($value, 'value');
    }
}
