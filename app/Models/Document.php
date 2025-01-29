<?php

namespace App\Models;

use App\Observers\DocumentsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends BaseModel
{
    public static function boot() 
    {
        parent::boot();
        Document::observe(new DocumentsObserver());
    }
    
    public function getAbsolutePathAttribute()
    {
        if ($this->disk === 's3') {
            return $this->path;
        } else {
            $public = $this->is_public ? 'public/' : '';
            return storage_path('app/'.$public.$this->path);
        }
    }
    
    public function getRelativePathAttribute()
    {
        if ($this->disk === 's3') {
            return Storage::disk('s3')->has($this->path) ? Storage::disk('s3')->url($this->path) : asset('img/contact_no_profile_image.png');
        } else {
            return file_exists($this->absolute_path) ? asset('storage/'.$this->path) : asset('img/contact_no_profile_image.png');
        }
    }
    
    public function getUrlAttribute()
    {
        return $this->relative_path;
    }
}
