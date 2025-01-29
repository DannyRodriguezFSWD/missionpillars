<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankInstitution extends BaseModel
{
    use SoftDeletes;

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function accounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function bankTransaction()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
