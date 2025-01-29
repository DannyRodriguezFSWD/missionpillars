<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends BaseModel
{
    use SoftDeletes;
//    protected $fillable = ['hidden'];
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('hidden', function ($builder) {
            $builder->where('hidden', false);
        });
    }
    
    
    
    /** relations **/
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function institution()
    {
        return $this->belongsTo(BankInstitution::class);
    }

    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'id');
    }
    
    /** Depreciated - transaction can only have one account **/
    public function accounts()
    { 
        \App\Classes\MissionPillarsLog::deprecated(['message'=>'Use singlular account']);
        return $this->account();
    }
    
    
    
    /** Scopes **/
    
    public function scopeUnmapped($query) 
    {
        return $query->where('mapped',0)->whereNull('register_id');
    }
}
