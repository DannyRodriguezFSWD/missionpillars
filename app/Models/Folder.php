<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\FoldersTrait;

class Folder extends BaseModel
{
    use FoldersTrait;
    
    protected $fillable = ['name', 'parent_folder_id', 'type'];
    public function __construct() {
        parent::__construct();
        parent::boot();
    }
    
    public function groups() {
        return $this->hasMany(Group::class);
    }
    
    public function getTagsChildrenFolders() {
        return $this->hasMany(Folder::class, 'folder_parent_id', 'id')->where('type', 'TAGS');
    }
    
    public function getGroupsChildrenFolders() {
        return $this->hasMany(Folder::class, 'folder_parent_id', 'id')->where('type', 'GROUPS');
    }
    
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->hasMany(Tag::class);
    }
    
}
