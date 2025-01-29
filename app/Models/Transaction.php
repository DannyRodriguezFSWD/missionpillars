<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Observers\Transactions\TransactionsObserver;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Transaction extends BaseModel
{
    use SoftDeletes;
    protected $observables = [
        'pledgeTransaction',
    ];

    public static function boot() {
        parent::boot();
        Transaction::observe(new TransactionsObserver());
    }
    
    public function transactionAltIds() {
        return $this->hasMany(AltId::class);
    }
    
    public function recurring() {
        return $this->belongsTo(TransactionTemplate::class, 'transaction_template_id', 'id')->where('is_recurring', true);
    }
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    public function paymentOption() {
        return $this->belongsTo(PaymentOption::class);
    }
    
    public function splits() {
        return $this->hasMany(TransactionSplit::class);
    }
    
    public function template() {
        return $this->belongsTo(TransactionTemplate::class, 'transaction_template_id', 'id');
    }
    
    public function pledge() {
        return $this->belongsToMany(TransactionTemplate::class, 'pledge_transactions');
    }
    
    public function softCredits()
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id', 'id');
    }
    
    public function parent()
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id', 'id');
    }
    
    /** Scopes **/
    
    /**
     * Filters acknowledged transactions
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  boolean $acknowledged Optional. If specified as false, includes only unacknowledged transactions. Otherwise, includes acknowledged
     * @param [null|string] $threshold Optional. If a timestamp is specified as a string, it is used to compare the date that is acknowledged. 
     */
    public function scopeAcknowledged($query, $acknowledged = true, $threshold = null) {
        if ($threshold) {
            if ($acknowledged) { 
                // transactions acknowledged before threshold
                return $query->where('acknowledged', true)
                ->where('acknowledged_at', '<=', $threshold);
            } 
            else {
                // transactions not acknowledged presently or acknowledged after the threshold
                return $query->where(function($query) use ($threshold) {
                    $query->where('acknowledged', false)
                    ->orWhere('acknowledged_at', '>=', $threshold);
                });
            }
        }
        return $query->where('acknowledged',$acknowledged);
    }
    
    /**
     * Filters transactions on or after between specified start date.
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  string|Carbon $start 
     * @return 
     */
    public function scopeOnOrAfter($query, $start) {
        if ($start && is_string($start)) $start = Carbon::parse($start);
        if (get_class($start) != Carbon::class) return $query;
        return $query->where( 'transaction_initiated_at', '>=', $start->startOfDay() );
    }
    
    /**
     * Filters transactions on or before specified end date. Note the end date includes up to the end of that date (e.g., 12/31/2018 includes 12/31/2018 11:59pm)
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  string|Carbon $end 
     * @return 
     */
    public function scopeOnOrBefore($query, $end) {
        if ($end && is_string($end)) $end = Carbon::parse($end);
        if (get_class($end) != Carbon::class) return $query;
        return $query->where( 'transaction_initiated_at', '<=', $end->endOfDay() );
    }
    
    /**
     * Filters transactions between specified start and end date. Note the end date includes up to the end of that date (e.g., 12/31/2018 includes 12/31/2018 11:59pm)
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  Array $between and array with two indices: Carbon $start, Carbon $end   
     * @return 
     */
    public function scopeBetween($query, array $between) {
        extract($between);
        return $query->whereBetween('transaction_initiated_at', [
            $start->startOfDay(), $end->endOfDay()
        ]);
    }
    
    /**
     * Filters tranactions by list of Contact
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  Array $contact_ids 
     */
    public function scopeContactIdIn($query, $contact_ids) {
        return $query->whereIn('contact_id',$contact_ids);
    }
    
    /**
     * Filters complete transactions
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  boolean $iscomplete Optional. If specified as false, includes only incomplete transactions. Otherwise, includes completed
     */
    public function scopeCompleted($query, $iscomplete = true) {
        return $query->statusIs('complete',$iscomplete);
    }
    
    
    /**
     * Filters offline transactions (made in MP)
     * @param  [type] $query Laravel automagically passes the query/builder
     */
    public function scopeOffline($query) {
        return $query->whereNotIn('channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give']);
    }
    
    /**
     * Filters online transactions (synced from CTG)
     * @param  [type] $query Laravel automagically passes the query/builder
     */
    public function scopeOnline($query) {
        return $query->whereIn('channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give']);
    }
    
    /**
     * Filters transactions by Status
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  [string] $status the value of status to filter by
     * @param  boolean $equals Optional. If specified as false, includes only transactions where status anything other than $status. Otherwise, filters transactions by $status
     */
    public function scopeStatusIs($query, $status, $equals = true) {
        if (!$equals) return $query->where('status','!=',$status);
        return $query->where('status',$status);
    }
    
    /**
     * Filters only tagged transactions with specified Ids
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array $tags an array of tags
     */
    public function scopeTaggedWithIds($query, array $tag_ids) {
        $query->whereHas('splits', function ($query) use ($tag_ids) {
            $query->taggedWithIds($tag_ids);
        });
    }
    
    /**
     * Filters out transactions tagged with specified Ids
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array $tags an array of tags
     */
    public function scopeNotTaggedWithIds($query, array $tag_ids) {
        $query->whereHas('splits', function ($query) use ($tag_ids) {
            $query->notTaggedWithIds($tag_ids);
        });
    }
    
    /**
     * Filters only tax_deductible transactions
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  boolean $taxdeductible Optional. If specified as false, includes only non-tax_deductible transactions. Otherwise, includes only tax deductible transactions 
     */
    public function scopeTaxDeductible($query, $taxdeductible = true) {
        return $query->whereHas('splits', function($query) use ($taxdeductible) {
            $query->where('tax_deductible', $taxdeductible);
        })->where('status', 'complete');
    }
    
    
    /** Accessors and Mutators **/
    
    public function setAcknowledgedAttribute($value) {
        if ($value) $this->attributes['acknowledged_at'] = gmdate('Y-m-d G:i:s');
        else  $this->attributes['acknowledged_at'] = null;
        
        $this->attributes['acknowledged'] = $value;
    }
    
    public function getTransactionTypeAttribute()
    {
        if ($this->parent_transaction_id) {
            return __('Soft Credit');
        } elseif ($this->template->is_recurring) {
            return __('Recurring');
        } else {
            return __('Normal Donation');
        }
    }
}
