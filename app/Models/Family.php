<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'families';
    
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
    
    public function image()
    {
        return $this->hasOne(Document::class, 'id', 'image_id');
    }
    
    public function getImagePathAttribute()
    {
        return ($this->image_id && $this->family_image_src !== asset('img/contact_no_profile_image.png')) ? $this->family_image_src : null;
    }
    
    public function getFamilyImageSrcAttribute()
    {
        if ($this->image_id) {
            return $this->image->url;
        } else {
            return asset('img/contact_no_profile_image.png');
        }
    }
}
