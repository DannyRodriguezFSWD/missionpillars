<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TagsTrait;

class Tag extends BaseModel
{
    use SoftDeletes, TagsTrait;

    public function __construct() {
        parent::__construct();
        parent::boot();
    }

    public function contacts() {
        return $this->belongsToMany(Contact::class);
    }

    public function lists() {
        return $this->belongsToMany(Lists::class, 'list_tags', 'tag_id', 'list_id');
    }

    public function notInList() {
        return $this->belongsToMany(Tag::class, 'list_not_tags', 'tag_id', 'list_id');
    }

    public function folder() {
        return $this->belongsTo(Folder::class);
    }

    
    /** Scopes **/
    
    public function scopeAutogenerated($query) {
        return $query->whereNotNull('relation_type');
    }
}
