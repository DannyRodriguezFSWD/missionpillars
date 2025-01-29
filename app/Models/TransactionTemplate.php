<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Settings\SettingValue;
use App\Classes\Settings;
use App\Classes\Email\EmailQueue;

class TransactionTemplate extends BaseModel {

    use SoftDeletes;

    
    /** Relationships **/
    public function splits() {
        return $this->hasMany(TransactionTemplateSplit::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function pledgedTransactions() {
        return $this->belongsToMany(Transaction::class, 'pledge_transactions');
    }

    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    
    /** Scopes **/
    
    public function scopeComplete($query) {
        return $query->where('status','complete');
    }
    
    public function scopeIncomplete($query) {
        return $query->where(function ($q) {
            $q->where('status','!=','complete')
            ->orWhereNull('status');
        });
    }
    
    public function scopePledges($query) {
        return $query->where('is_pledge',true);
    }
    
    public function scopeRecurring($query) {
        return $query->where('is_recurring',true);
    }
    
    // order by scope
    public function scopeOrderByStatus($query, $direction = 'ASC') {
        return $query->orderBy(DB::raw('CASE
        WHEN is_recurring IS NULL OR is_recurring = 0 THEN NULL
        WHEN transaction_templates.billing_cycles IS NOT NULL AND transaction_templates.billing_cycles = transaction_templates.successes THEN "complete"
        WHEN transaction_templates.subscription_terminated IS NOT NULL THEN "canceled"
        WHEN transaction_templates.subscription_suspended IS NOT NULL THEN "paused" 
        WHEN transaction_templates.status IS NULL THEN "active"
        ELSE transaction_templates.status
        END'), $direction);
    }
    /** other methods **/
    
    /**
     * TODO move to a trait
     */
    public function addPledgedTransaction($transaction) {
        if (array_get($this, 'status') !== 'complete') {
            $this->pledgedTransactions()->sync([array_get($transaction, 'id')], false);
            $transaction->fireEvent('pledgeTransaction');
            $transactions = TransactionSplit::join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                    ->join('pledge_transactions', 'pledge_transactions.transaction_id', '=', 'transactions.id')
                    ->join('transaction_templates', 'transaction_templates.id', '=', 'pledge_transactions.transaction_template_id')
                    ->select(DB::raw('SUM(transaction_splits.amount) as total'))
                    ->where([
                        ['pledge_transactions.transaction_template_id', '=', array_get($this, 'id')],
                        ['transaction_templates.tenant_id', '=', array_get(auth()->user(), 'tenant.id')]
                    ])->whereNull('transaction_templates.deleted_at')
                    ->first();
            
            $total = array_get($this, 'splits.0.amount', 0) * array_get($this, 'billing_cycles', 1);
            
            if (array_get($transactions, 'total', 0) >= $total) {
                array_set($this, 'status', 'complete');
            }

            if (array_get($transactions, 'total', 0) > 0 && array_get($transactions, 'total', 0) < $total) {
                array_set($this, 'status', 'active');
            }
            $this->update();
        }
    }

    
    /** accessors **/
    public function getStatusAttribute($value) {
        if (!$this->is_recurring) return $value;
        elseif ($value === null) return 'active';
        elseif ($this->billing_cycles && $this->billing_cycles == $this->successes) return 'complete';
        elseif ($this->subscription_terminated) return 'canceled';
        elseif ($this->subscription_suspended) return 'paused';
        
        return $value;
    }
    
    /**
     * Calculates the billing_end_date (which is typically set to NULL in the database)
     * TODO consider populating the billing_end_date in CTG and defaulting to that if available
     * NOTE For this accessor to work the following attributes must be loaded (in the case of a limited select statement): is_recurring, billing_cycles, subscription_terminated, subscription_suspended, billing_start_date, billing_period
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getBillingEndDateAttribute($value) {
        if (!$this->is_recurring || !$this->billing_cycles) return $value;
        if ($this->subscription_terminated || $this->subscription_suspended) return null;
        
        $period = $this->billing_period == 'Bi-Week' ? "Week" : $this->billing_period;
        $cycles = $this->billing_period == 'Bi-Week' ? 2 * $this->billing_cycles : $this->billing_cycles;
        $enddate = \Carbon\Carbon::parse($this->billing_start_date)->{"add{$period}s"}($cycles);
        return $enddate->format('Y-m-d H:i:s');
    }
    
    
}
