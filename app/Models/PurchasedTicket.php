<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasedTicket extends BaseModel
{
    use SoftDeletes;
    
    /**
     * returns EventRegistry::class
     */
    public function registry()
    {
        return $this->belongsTo(EventRegister::class, 'calendar_event_contact_register_id', 'id');
    }
    
    public function formEntry()
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id', 'id');
    }

    public function ticketOption()
    {
        return $this->belongsTo(TicketOption::class, 'ticket_option_id', 'id');
    }
}
