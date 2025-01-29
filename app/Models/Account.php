<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends BaseModel
{
    use SoftDeletes;

    /** relationships **/
    /*** belongs to ***/
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function accountGroup() {
        return $this->belongsTo(AccountGroup::class);
    }
    
    public function accountGroups() {
        \App\Classes\MissionPillarsLog::deprecated(['message'=>'Use singular accountGroup relation instead']);
        return $this->accountGroup();
    }

    public function fund() {
        return $this->belongsTo(Fund::class, 'account_fund_id');
    }
    
    public function group() {
        return $this->belongsTo(AccountGroup::class, 'account_group_id', 'id');
    }
    
    public function parentAccount() {
        return $this->belongsTo(self::class, 'parent_account_id');
    }
    
    
    /*** has ***/
    
    /**
     * Returns any bank accounts that are linked with the account.
     * TODO currently this is plural and some tenants may have a one to many 
     * relationship between the BankAccount and Account; however, conceptually this should be one to one
     */
    public function linkedBankAccounts() {
        return $this->hasMany(BankAccount::class);
    }
    
    public function startingBalance() {
        return $this->hasMany(StartingBalance::class);
    }
    
    public function subAccounts() {
        return $this->hasMany(self::class, 'parent_account_id');
    }
    
    public function transactions()
    {
        return $this->hasMany(Register::class, 'account_register_id');
    }

    
    
    /*** Scopes ***/
    
    public function scopeAssets($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','asset');
        });
    }
    
    public function scopeAccountsReceivable($query) {
        return $query->where('account_type','accounts_receivable');
    }
    
    public function scopeIncome($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','income');
        });
    }
    
    public function scopeEquities($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','equity');
        });
    }
    
    public function scopeExpense($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','expense');
        });
    }
    
    public function scopeLiabilities($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','liability');
        });
    }
    
    public function scopeRegisters($query) {
        return $query->where('account_type','register');
    }
    
    // Negative scopes
    
    public function scopeNotAssets($query) {
        return $query->whereHas('accountGroup', function($q) {
            $q->where('chart_of_account','!=','asset');
        });
    }
    
    public function scopeNotAccountsReceivable($query) {
        return $query->where(function($q) {
            $q->where('account_type','!=','accounts_receivable')
            ->orWhereNull('account_type');
        });
    }
    
    public function scopeNotIncome($query) {
        return $query->whereHas('accountGroup', function($q) {
            $q->where('chart_of_account','!=','income');
        });
    }
    
    public function scopeNotEquities($query) {
        return $query->whereHas('accountGroup',function($q) {
            $q->where('chart_of_account','!=','equity');
        });
    }
    
    public function scopeNotExpense($query) {
        return $query->whereHas('accountGroup', function($q) {
            $q->where('chart_of_account','!=','expense');
        });
    }
    
    public function scopeNotLiabilities($query) {
        return $query->whereHas('accountGroup', function($q) {
            $q->where('chart_of_account','!=','liability');
        });
    }
    
    public function scopeNotRegisters($query) {
        return $query->where(function($q) {
            $q->where('account_type','!=','register')
            ->orWhereNull('account_type');
        });
    }
    
    
    // relation scopes
    
    public function scopeLinked($query) {
        return $query->has('linkedBankAccounts');
    }
    
    public function scopeUnlinked($query) {
        return $query->doesntHave('linkedBankAccounts');
    }
    
}
