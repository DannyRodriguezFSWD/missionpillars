<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Lists\ListTrait;

// TODO It might be problematic to do so, but consider renaming MPList (List and list is reserved)
class Lists extends BaseModel
{
    use SoftDeletes, ListTrait;
    protected $table = 'lists';
    protected $auto_save_tenant_id = true;
    
    /*** Relationships ***/
    
    public function inTags() {
        return $this->belongsToMany(Tag::class, 'list_tags', 'list_id', 'tag_id');
    }
    
    public function notInTags() {
        return $this->belongsToMany(Tag::class, 'list_not_tags', 'list_id', 'tag_id');
    }
    
    public function emailsSent() {
        return $this->hasMany(Email::class, 'list_id');
    }
    
    public function datatableState() {
        return $this->belongsTo(DatatableState::class);
    }
    
    
    /*** Scopes ***/
    public function scopeSavedSearch($query) {
        return $query->whereHas('datatableState');
    }
    
    public function scopeUserSavedSearch($query, $test = true) {
        return $query->whereHas('datatableState', function($state) use ($test) {
            $state->isUserSearch($test);
        });
    }
    
    public function scopeLegacy($query) {
        return $query->whereNull('datatable_state_id');
    }
}
