<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Observers\PurposeObserver;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purpose extends BaseModel {
    use SoftDeletes;
    
    protected $observables = ['retrieveTag'];
    public $tag = null;
    
    public static function boot() {
        parent::boot();
        Purpose::observe(new PurposeObserver());
    }

    public function purposesAltIds() {
        return $this->hasMany(PurposesAltId::class);
    }
    
    public function parentPurpose() {
        return $this->belongsTo(Purpose::class, 'parent_purposes_id', 'id');
    }
    public function getParent() { return $this->parentPurpose(); }
    
    public function childPurposes() {
        return $this->hasMany(Purpose::class, 'parent_purposes_id', 'id');
    }
    public function getChildren() { return $this->childPurposes(); }
    
    public function transactions() {
        return $this->hasMany(TransactionSplit::class);
    }
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function account(){
        return $this->belongsTo(Account::class);
    }

    public function fund(){
        return $this->belongsTo(Fund::class);
    }
    
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }    
    
    /***** Scopes *****/
    
    public function scopeParent($query) {
        $query->whereNull('parent_purposes_id');
    }
    
    public function scopeChild($query) {
        $query->whereNotNull('parent_purposes_id');
    }

    public function scopeReceivesDonations($query)
    {
        $query->where('receive_donations',1)
        //HACK We have to keep the general purpose
        ->orWhere('id',1);
    }
}
