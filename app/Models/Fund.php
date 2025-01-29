<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fund extends BaseModel
{
    use SoftDeletes;
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function accounts() {
        return $this->hasMany(Account::class);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }
}
