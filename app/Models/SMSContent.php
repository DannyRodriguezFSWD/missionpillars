<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSContent extends BaseModel
{
    protected $table = 'sms_content';
    protected $auto_save_tenant_id = true;

    public function includeTags() {
        return $this->belongsToMany(Tag::class, 'sms_include_tags', 'sms_content_id');
    }
    
    public function excludeTags() {
        return $this->belongsToMany(Tag::class, 'sms_exclude_tags', 'sms_content_id');
    }
    
    public function lists() {
        \App\Classes\MissionPillarsLog::deprecated();
        return $this->belongsTo(Lists::class, 'list_id', 'id');
    }

    public function list() {
        return $this->belongsTo(Lists::class, 'list_id', 'id');
    }

    public function sent() {
        return $this->hasMany(SMSSent::class, 'sms_content_id');
    }
    
    public function SMSPhoneNumberFrom()
    {
        return $this->belongsTo(SMSPhoneNumber::class, 'sms_phone_number_from', 'phone_number');
    }
    
    public function SMSPhoneNumberTo()
    {
        return $this->belongsTo(SMSPhoneNumber::class, 'sms_phone_number_to', 'phone_number');
    }
    
    public function scopeIsScheduled($query)
    {
        return $query->where('time_scheduled', '>', date('Y-m-d H:i:s'));
    }
    
    public function scopeIsNotScheduled($query)
    {
        return $query->where('time_scheduled', '<=', date('Y-m-d H:i:s'))->orWhereNull('time_scheduled');
    }
    
    public function getIsScheduledAttribute()
    {
        return strtotime($this->time_scheduled) > strtotime(date('Y-m-d H:i:s'));
    }
}
