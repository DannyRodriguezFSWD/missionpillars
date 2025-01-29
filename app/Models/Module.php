<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    
    
    /** relationships **/
    public function features(){
        return $this->belongsToMany(Feature::class, 'module_features');
    }
    
    public function scopeCRM($query) {
        return $query->where('id', 2);
    }
    
    public function scopeAccounting($query) {
        return $query->where('id', 3);
    }
}
