<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends BaseModel
{
    use SoftDeletes;

    protected $appends = ['bank_institution', 'plaid_error_full'];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function institution()
    {
        return $this->belongsTo(BankInstitution::class, 'bank_institution_id', 'id')->withTrashed();
    }

    public function account()
    {
        \App\Classes\MissionPillarsLog::deprecated(['message'=>'Use crmAccount instead']);
        return $this->crmAccount();
    }

    public function crmAccount()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function bankTransaction()
    {
        return $this->hasMany(BankTransaction::class);
    }
    
    public function getBankInstitutionAttribute()
    {
        return array_get($this->institution, 'bank_institution');
    }
    
    public function getPlaidErrorFullAttribute()
    {
        $error = null;
        
        if ($this->plaid_error_code) {
            $error = $this->plaid_error_code;
            
            if ($this->plaid_error_message) {
                $error.= ' - '.$this->plaid_error_message;
            }
        }
        
        return $error;
    }
}
