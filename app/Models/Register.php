<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class Register extends BaseModel
{
    use SoftDeletes;

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_register_id');
    }
    
    public function bankTransaction()
    {
        return $this->hasOne(BankTransaction::class);
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function splits()
    {
        return $this->hasMany(RegisterSplit::class);
    }
    
    public function cleanSplits()
    {
        return $this->hasMany(RegisterSplit::class)->where('splits_partner_id', '!=', null);
    }
    
    
    /** Scopes **/
    
    public function scopeJournalEntries($query) {
        return $query->whereIn('register_type', ['journal_entry', 'fund_transfer']);
    }
    
    public function scopeFundTransfers($query) {
        return $query->where('register_type', 'fund_transfer');
    }
    
    /** STATIC HELPER METHODS **/
    
    /**
     * Returns the maximum (scoped) journal_entry_id value, or 0 if none exists 
     * @return [integer] 
     */
    public static function maxJournalEntryId() {
        return Register::withTrashed()->journalEntries()
        ->max('journal_entry_id') ?: 0;
    }
    
    /**
     * Returns the next (scoped) journal_entry_id value (1 if no others exist) 
     * @return [integer] 
     */
    public static function nextJournalEntryId() {
        return self::maxJournalEntryId() + 1;
    }
}
