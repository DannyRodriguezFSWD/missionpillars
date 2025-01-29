<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagFolder extends BaseModel {
    
    public function __construct() {
        parent::__construct();
        parent::boot();
    }
    
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->hasMany(Tag::class);
    }
    
    public function children() {
        return $this->hasMany(TagFolder::class, 'folder_parent_id', 'id')->where('type', 'TAGS');
    }

}
