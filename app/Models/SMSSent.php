<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSSent extends BaseModel
{
    use SoftDeletes;
    
    protected $table = 'sms_sent';

    public function to() {
        return $this->belongsTo(Contact::class, 'to_contact_id');
    }

    public function from() {
        return $this->belongsTo(Contact::class, 'from_contact_id');
    }
    
    public function content() {
        return $this->belongsTo(SMSContent::class, 'sms_content_id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tenant(){
        return $this->belongsTo(Tenant::class);
    }
    
    public function getPhoneNumberFromAttribute()
    {
        if (array_get($this->content, 'SMSPhoneNumberFrom.name_and_number')) {
            return array_get($this->content, 'SMSPhoneNumberFrom.name_and_number');
        } else {
            return array_get($this->content, 'sms_phone_number_from');
        }
    }
    
    public function getPhoneNumberToAttribute()
    {
        if (array_get($this->content, 'sms_phone_number_to')) {
            return array_get($this->content, 'SMSPhoneNumberTo.name_and_number');
        } else {
            return array_get($this->to, 'full_name_phone');
        }
    }
}
