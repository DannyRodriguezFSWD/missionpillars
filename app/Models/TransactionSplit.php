<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tag;
use App\Observers\TransactionSplitsObserver;

class TransactionSplit extends BaseModel
{
    use SoftDeletes;

    public static function boot() {
        parent::boot();
        TransactionSplit::observe(new TransactionSplitsObserver());
    }

    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->belongsToMany(Tag::class)
        ->withTimestamps();
    }

    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionTemplate() {
        return $this->belongsTo(TransactionTemplate::class);
    }

    public function transactionTemplateSplit(){
        return $this->belongsTo(TransactionTemplateSplit::class);
    }

    public function purpose() {
        return $this->belongsTo(Purpose::class);
    }

    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function givingFor() {
        $for = [];
        /*
        if ($this->campaign_id) {
            array_push($for, $this->campaign->name);
        }
        */
        $chart = $this->purpose;
        while ($chart) {
            array_push($for, $chart->name);
            $chart = $chart->getParent;
        }
        /*
        if(count($for) > 0 && $for[0] === 'None'){
            unset($for[0]);
        }
         *
         */
        return implode(' / ', array_reverse($for));
    }

    public function registry(){
        return $this->belongsToMany(RegisterSplit::class, 'transactions_registers', 'transaction_split_id', 'register_split_id');
    }
    
    
    /** Scopes **/
    
    public function scopeCompleted($query) {
        return $query->whereHas('transaction', function ($query) {
            $query->completed();
        });
    }
    public function scopeDonations($query) {
        return $query->where('type', 'donation');
    }
    public function scopeForCampaign($query) {
        return $query->where('campaign_id','!=',1);
    }
    
    
    /**
     * Filters transaction splits on or after between specified start date.
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  string|Carbon $start 
     * @return 
     */
    public function scopeOnOrAfter($query, $start) {
        return $query->whereHas('transaction', function($q) use ($start) {
            $q->onOrAfter($start);
        });
    }
    
    /**
     * Filters transaction splits on or before specified end date. Note the end date includes up to the end of that date (e.g., 12/31/2018 includes 12/31/2018 11:59pm)
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  string|Carbon $end 
     * @return 
     */
    public function scopeOnOrBefore($query, $end) {
        return $query->whereHas('transaction', function($q) use ($end) {
            $q->onOrBefore($end);
        });
    }
    
    /**
     * Filters only tagged transactions with specified Ids
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array $tags an array of tags
     */
    public function scopeTaggedWithIds($query, array $tag_ids) {
        $query->whereHas('tags', function ($query) use ($tag_ids) {
            $query->whereIn('tag_id', $tag_ids);
        });
    }
    
    /**
     * Filters out transactions tagged with specified Ids
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array $tags an array of tags
     */
    public function scopeNotTaggedWithIds($query, array $tag_ids) {
        $query->whereHas('tags', function ($query) use ($tag_ids) {
            $query->whereIn('tag_id', $tag_ids);
        }, '=', 0);
    }
}
