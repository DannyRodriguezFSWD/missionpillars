<?php

namespace App\Models;

use App\Observers\CampaignsObserver;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends BaseModel
{
    use SoftDeletes;
    
    protected $observables = ['retrieveTag'];
    public $tag = null;
    
    public static function boot() {
        parent::boot();
        Campaign::observe(new CampaignsObserver());
    }
    
    public function campaignAltIds() {
        return $this->hasMany(CampaignAltId::class);
    }
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    public function purpose() {
        return $this->belongsTo(Purpose::class);
    }
    
    public function transactions() {
        return $this->hasMany(TransactionSplit::class);
    }

    public function scopeReceivesDonations($query)
    {
        $query->where('receive_donations',1)
            //HACK We have to keep the None
            ->orWhere('id',1);
    }

    public function scopeOrgOwned($query){
	// TODO ensure that https://app.asana.com/0/0/1199563989384204/f is resolved and uncomment next line
        //$query->whereNull('contact_id');
    }
    
    
    /**** Scopes ****/
    
    public function scopeExcludeNone($query) {
        return $query->where('id','!=',1);
    }
    
}
