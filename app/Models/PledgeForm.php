<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PledgeForm extends BaseModel
{
    use SoftDeletes;
    
    public static function boot() {
        parent::boot();
    }
    
    public function form() {
        return $this->belongsTo(Form::class);
    }
    
    public function purpose() {
        return $this->belongsTo(Purpose::class);
    }
    
    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }
}
