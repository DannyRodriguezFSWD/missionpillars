<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\GroupsObserver;

class Group extends BaseModel
{
    use SoftDeletes;
    
    public static function boot() {
        parent::boot();
        Group::observe(new GroupsObserver());
    }
    
    public function address() {
        return $this->hasOne(Address::class);
    }
    
    public function folder() {
        return $this->belongsTo(Folder::class);
    }
    
    public function tag() {
        return $this->belongsTo(Tag::class, 'map_tag_id');
    }
    
    public function contacts() {
        return $this->belongsToMany(Contact::class, 'group_contact')->withTimestamps();
    }
    
    public function leaders() {
        return $this->belongsToMany(Contact::class, 'contact_group_leader');
    }
    
    public function form() {
        return $this->belongsTo(Form::class);
    }
    
    public function calendar() {
        return $this->belongsTo(Calendar::class);
    }
    
    public function manager()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    
    public function tenant() 
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function getCoverImageSrcAttribute()
    {
        if ($this->cover_image && file_exists(storage_path('app/public/groups/'.$this->cover_image))) {
            return asset('storage/groups/'.array_get($this, 'cover_image'));
        } else {
            return asset('img/group_no_image.png');
        }
    }
    
    public function getFullAddressAttribute()
    {
        $address = $this->addresses->first();
        
        if ($address) {
            return trim($address->mailing_address_1.' '.$address->city.' '.$address->region.' '.$address->postal_code);
        } else {
            return null;
        }
    }
}
