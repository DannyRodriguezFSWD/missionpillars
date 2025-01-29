<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;

class TicketOption extends BaseModel
{
    use SoftDeletes;

    public function purchasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class);
    }
    
    public function getTotalNumberOfTicketsAttribute()
    {
        return $this->purchasedTickets()->count() + $this->availability;
    }
}
