<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tag;
use App\Observers\Pledges\PledgeObserver;
use App\Traits\GivingForTrait;

class TransactionTemplateSplit extends BaseModel
{
    use SoftDeletes, GivingForTrait;

    public static function boot() {
        parent::boot();
        TransactionTemplateSplit::observe(new PledgeObserver());
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

    public function template() {
        return $this->belongsTo(TransactionTemplate::class, 'transaction_template_id', 'id');
    }

    public function purpose() {
        return $this->belongsTo(Purpose::class);
    }

    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function transactionSplits() {
        return $this->hasMany(TransactionSplit::class);
    }
    public function transactionSplit() {
        return $this->hasOne(TransactionSplit::class);
    }



    public function contact() {
        return $this->belongsTo(Contact::class);
    }

}
