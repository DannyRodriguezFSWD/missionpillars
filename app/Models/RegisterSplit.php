<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Observers\RegisterSplitsObserver;

class RegisterSplit extends BaseModel
{
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        RegisterSplit::observe(new RegisterSplitsObserver());
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function register()
    {
        return $this->belongsTo(Register::class);
    }

    public function transactions(){
        return $this->belongsToMany(TransactionSplit::class, 'transactions_registers', 'register_split_id', 'transaction_split_id');
    }
}
