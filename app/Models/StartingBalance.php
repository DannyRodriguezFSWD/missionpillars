<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class StartingBalance extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['account_id', 'tenant_id', 'fund_id', 'blanace'];

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function account() {
        return $this->belongsTo(Account::class);
    }
}