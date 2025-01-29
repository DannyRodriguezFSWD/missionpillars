<?php

namespace App\Models;

use Zizaco\Entrust\EntrustRole;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends EntrustRole {
    use SoftDeletes;
    /*
    public static function boot() {
        parent::boot();
        static::addGlobalScope(new TenantScope);
    }
    */
    
    public function scopeGlobal($query) {
        return $query->whereNull('tenant_id');
    }
    
}
