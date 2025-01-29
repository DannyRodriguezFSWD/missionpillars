<?php

namespace App\Models;

class BankTransactionsAutofill extends BaseModel
{
    protected $table = 'bank_transactions_autofill';

    public function payee()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
