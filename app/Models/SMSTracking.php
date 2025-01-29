<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSTracking extends BaseModel
{
    protected $table = 'sms_tracking';

    public function contact(){
        return $this->belongsTo(Contact::class);
    }
}
