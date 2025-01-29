<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEventTemplateSplit extends BaseModel
{
    //use SoftDeletes;
    
    public function template() {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_template_id');
    }
    
    public function registries() {
        return $this->hasMany(EventRegister::class);
    }
    
    public function purchasedTickets() {
        return $this->hasManyThrough(PurchasedTicket::class, EventRegister::class, 'calendar_event_template_split_id', 'calendar_event_contact_register_id', 'id');
    }
    
    public function contactsCheckedIn() {
        return $this->hasManyThrough(PurchasedTicket::class, EventRegister::class, 'calendar_event_template_split_id', 'calendar_event_contact_register_id', 'id')
                ->where('checked_in', true);
    }
    
    public function contacts() {
        return $this->belongsToMany(Contact::class, 'calendar_event_contact_register')->withTimestamps();
    }
    
    public function getCheckinUrlAttribute()
    {
        $url = sprintf(env('APP_DOMAIN'), array_get($this, 'template.managers.0.tenant.subdomain')).'crm/checkin/';
        $url.= $this->template->group ? $this->template->group->uuid : 0;
        $url.= '/'.$this->uuid;
        return $url;
    }
}
