<?php
namespace App\Classes\Shared;

use App\Models\PurchasedTicket;
use Carbon\Carbon;

class TicketsTemporaryHold{

    public static function getTickets($request = null){
        if(is_null($request)){
            return PurchasedTicket::noTenantScope()->where([
                ['temporary_hold', '=', true],
                ['created_by_session_id', '=', session()->getId()],
                ['temporary_hold_ends_at', '>', Carbon::now()],
            ])->get();
        }
        
        return PurchasedTicket::noTenantScope()->where([
            ['temporary_hold', '=', true],
            ['created_by_session_id', '=', session()->getId()],
            ['temporary_hold_ends_at', '>', Carbon::now()],
            ['calendar_event_contact_register_id', '=', array_get($request, 'register_id')],
        ])->get();
    }
    
    public static function check(){
        $tickets = self::getTickets();
        return count($tickets) > 0;
    }

    public static function getTimeLeft($request = null){
        $tickets = self::getTickets($request);
        $temporary_hold_ends_at = Carbon::parse(array_get($tickets, '0.temporary_hold_ends_at'));
        $now = Carbon::now();
        $time_left = 0;
        if($temporary_hold_ends_at > $now){
            $time_left = $now->diffInSeconds($temporary_hold_ends_at);
        }
        return $time_left;
    }
}
