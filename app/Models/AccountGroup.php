<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends BaseModel
{
    use SoftDeletes;
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
    
    public function accounts() {
        return $this->hasMany(Account::class)->with('startingBalance')->orderBy('name','asc');
    }
}
