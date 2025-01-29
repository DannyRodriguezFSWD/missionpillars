<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatatableState extends BaseModel
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    protected $guarded = ['id','tenant_id'];
    protected $auto_save_tenant_id = true;
    protected $visible = [
        'id',
        'is_user_search',
        'time',
        'columns',
        'order',
        'start',
        'length',
        'search',
    ];

    /*** Relationships ***/

    public function list() {
        return $this->hasOne(Lists::class);
    }
    
    /*** Accessors ***/
    
    // json accessors NOTE to get DB value use $model->getAttributes()['columnname']
    public function getColumnsAttribute($value) { return json_decode($value, true); }
    public function getOrderAttribute($value) { return json_decode($value, true); }
    public function getSearchAttribute($value) { return json_decode($value, true); }
    
    
    /*** Mutators ***/
    
    // json mutators
    public function setColumnsAttribute($value) { $this->attributes['columns'] = self::getJson($value); }
    public function setOrderAttribute($value) { $this->attributes['order'] = self::getJson($value); }
    public function setSearchAttribute($value) { $this->attributes['search'] = self::getJson($value); }
    
    
    /*** Scopes ***/
    
    public function scopeForCurrentUser($query) {
        return $this->scopeForUser($query, auth()->user()->id);
    }
    
    public function scopeForUri($query, $uri) {
        return $query->where('uri', $uri);
    }
    
    public function scopeForUser($query, $user_id) {
        return $query->where('created_by', $user_id);
    }
    
    public function scopeIsUserSearch($query, $test = true) {
        return $query->where('is_user_search', $test);
    }
    
    
    
    /*** Utility methods ***/
    
    protected static function getJson($value) {
        return ( is_string($value) && is_object(json_decode($value)) )
        ? $value : json_encode($value);
    }
}
