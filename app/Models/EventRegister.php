<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegister extends BaseModel
{
    protected $table = 'calendar_event_contact_register';

    public function tenant(){
    	return $this->BelongsTo(Tenant::class);
    }

    public function contacts(){
    	return $this->belongsToMany(Contact::class);
    }
    
    public function contact(){
    	return $this->BelongsTo(Contact::class);
    }

    public function event(){
    	return $this->BelongsTo(CalendarEventTemplateSplit::class, 'calendar_event_template_split_id');
    }

    public function transaction(){
    	return $this->BelongsTo(Transaction::class);
    }
    /**
     * 
     * @return Array of PurchasedTicket::class
     */
    public function tickets() {
        return $this->hasMany(PurchasedTicket::class, 'calendar_event_contact_register_id');
    }
    
    public function releasedTickets() 
    {
        return $this->hasMany(PurchasedTicket::class, 'calendar_event_contact_register_id')->onlyTrashed();
    }
    
    /**
     * 
     * @return PurchasedTicket::class
     */
    public function ticket() {
        return $this->hasOne(PurchasedTicket::class, 'calendar_event_contact_register_id');
    }
    
    public function checkInContacts() {
        return $this->belongsToMany(Contact::class, 'calendar_event_contact_check_in')->withTimestamps();
    }
}
