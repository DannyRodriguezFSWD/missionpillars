<?php

namespace App\Classes\Events;

use App\Classes\IcsExport\IcsExport;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\EventRegister;
use App\Models\TicketOption;
use App\Models\PurchasedTicket;
use App\Models\Contact;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Description of EventSignin
 *
 * @author josemiguel
 */
class EventSignin extends Events {

    public static function checkReservation($request){
        $registry = EventRegister::find(array_get($request, 'registry'));
        $event = array_get($registry, 'event.template');

        if(empty($event) || ($event && !array_get($event, 'allow_reserve_tickets'))){
            return null;//we let them through
        }

        $reserved_tickets = PurchasedTicket::where('calendar_event_contact_register_id', array_get($registry, 'id'))->withTrashed()->get(['id','deleted_at','temporary_hold_ends_at']);
        $redirect = \App\Classes\Redirections::get();
        if(count($reserved_tickets) <= 0){

            if(!empty($redirect)){
                return redirect($redirect)->with('error', __('Ticket reservation not found'));
            }

            if(!empty($registry)){
                return redirect()->route('events.share', ['id' => array_get($registry, 'event.uuid')])->with('error', __('Ticket reservation not found'));
            }

            return redirect('/')->with('error', __('Ticket reservation not found'));
        }else{
            $isExpired = $reserved_tickets->first()->temporary_hold_ends_at <= Carbon::now();
            if ($isExpired && !empty($redirect)) return redirect($redirect ?: '/')->with('error', __('Ticket reservation expired'));
        }
    }

    public static function releaseTickets() 
    {
        $now = Carbon::now();
        
        $tickets = PurchasedTicket::withoutGlobalScopes()
            ->where('temporary_hold', true)
            ->where('temporary_hold_ends_at', '<=', $now)
            ->whereNull('deleted_at')
            ->whereHas('registry', function ($query) {
                $query->withoutGlobalScopes()   
                    ->whereHas('event', function ($query) {
                        $query->withoutGlobalScopes()
                            ->whereHas('template', function ($query) {
                                $query->withoutGlobalScopes()
                                    ->where('pay_later', 0);
                            });
                    });
            })
            ->whereHas('ticketOption', function ($query) {
                $query->withoutGlobalScopes();
            })
            ->get();
        
        foreach ($tickets as $ticket) {
            $option = TicketOption::withoutGlobalScopes()->where('id', array_get($ticket, 'ticket_option_id'))->first();
            
            if (!empty($option)) {
                $availability = array_get($ticket, 'amount', 0) + array_get($option, 'availability', 0);
                DB::table('ticket_options')->where('id', array_get($option, 'id'))->update(['availability' => $availability]);
            }
        }
        
        $ids = $tickets->pluck('id');
        DB::table('purchased_tickets')->whereIn('id', $ids)->update(['deleted_at' => $now]);
    }

    public static function setRegistry($event, $contact) {
        $registry = new EventRegister();
        array_set($registry, 'tenant_id', array_get($event, 'template.tenant_id'));
        array_set($registry, 'contact_id', array_get($contact, 'id'));
        array_set($registry, 'calendar_event_template_split_id', array_get($event, 'id'));

        if ($registry->save()) {
            return $registry;
        }
        return null;
    }

    public static function setTicket($event, $registry, $ticketOption, $checkin = false) {
        $ticket = new PurchasedTicket();
        array_set($ticket, 'tenant_id', array_get($event, 'template.tenant_id'));
        array_set($ticket, 'calendar_event_contact_register_id', array_get($registry, 'id'));
        array_set($ticket, 'ticket_name', array_get($ticketOption, 'name'));
        array_set($ticket, 'ticket_option_id', array_get($ticketOption, 'id'));
        array_set($ticket, 'price', array_get($ticketOption, 'price'));
        array_set($ticket, 'amount', 1);
        array_set($ticket, 'uuid', Uuid::uuid1());


        // $allow_unlimited_tickets = array_get($ticketOption, 'allow_unlimited_tickets', false);
        // $is_free_ticket = array_get($ticketOption, 'is_free_ticket', false);
        // if(!$allow_unlimited_tickets || !$is_free_ticket){
            array_set($ticket, 'temporary_hold', true);
            array_set($ticket, 'temporary_hold_ends_at', Carbon::now()->addMinutes(env('TICKETS_TEMPORARY_HOLD', 10)));
        // }

        // commenting this because it was checking in the registry as soon as someone pressed sign up before even getting the contact info
//        array_set($ticket, 'checked_in', $checkin);
//        array_set($ticket, 'used', $checkin);
//        if ($checkin) {
//            array_set($ticket, 'used_at', Carbon::now());
//        }

        if ($registry->tickets()->save($ticket)) {
            if(!array_get($ticketOption, 'allow_unlimited_tickets', false)){
                if(array_get($ticketOption, 'availability') > 0){
                    $availability = array_get($ticketOption, 'availability') - 1;
                    array_set($ticketOption, 'availability', $availability);
                    $ticketOption->update();
                }
            }
            return $ticket;
        }

        return null;
    }

    /**
     * Using this instead of setTicket for events that don't have a ticket so they create a purchased tickets row anyway
     *
     * @param type $event
     * @param type $registry
     * @return PurchasedTicket
     */
    public static function setFakeTicket($event, $registry) {
        $ticket = new PurchasedTicket();
        array_set($ticket, 'tenant_id', array_get($event, 'template.tenant_id'));
        array_set($ticket, 'calendar_event_contact_register_id', array_get($registry, 'id'));
        array_set($ticket, 'ticket_name', array_get($event, 'template.name'));
        array_set($ticket, 'amount', 1);
        array_set($ticket, 'uuid', Uuid::uuid1());
        array_set($ticket, 'temporary_hold', true);
        array_set($ticket, 'temporary_hold_ends_at', Carbon::now()->addMinutes(env('TICKETS_TEMPORARY_HOLD', 10)));
        array_set($ticket, 'checked_in', false);
        array_set($ticket, 'used', false);

        if ($registry->tickets()->save($ticket)) {
            return $ticket;
        }

        return null;
    }

    /**
     * Used when event allow_reserve_tickets = false and allow_auto_check_in = true
     * @param mixed $id
     * @param Request $request
     */
    public static function autoCheckInWithoutTickets($id, $request) {
        $event = CalendarEventTemplateSplit::where('uuid', $id)->first();
        if (is_null($event)) {
            abort(404);
        }
        $ticketOption = array_get($event, 'template.ticketOptions.0');
        $contact = Contact::findOrFail(array_get($request, 'id'));

        $registry = new EventRegister();
        array_set($registry, 'tenant_id', array_get($event, 'template.tenant_id'));
        array_set($registry, 'contact_id', array_get($contact, 'id'));
        array_set($registry, 'calendar_event_template_split_id', array_get($event, 'id'));

        if ($registry->save() && !is_null($ticketOption)) {
            $ticket = new PurchasedTicket();
            array_set($ticket, 'tenant_id', array_get($event, 'template.tenant_id'));
            array_set($ticket, 'calendar_event_contact_register_id', array_get($registry, 'id'));
            array_set($ticket, 'ticket_name', array_get($ticketOption, 'name'));
            array_set($ticket, 'ticket_option_id', array_get($ticketOption, 'id'));
            array_set($ticket, 'price', array_get($ticketOption, 'price'));
            array_set($ticket, 'amount', 1); //since it is auto check in, always be 1
            array_set($ticket, 'uuid', Uuid::uuid1());
            array_set($ticket, 'checked_in', true);
            array_set($ticket, 'used', true);
            array_set($ticket, 'used_at', Carbon::now());
            $registry->tickets()->save($ticket);

            static::sendEmailToManager($event, $registry);

            if (array_get($event, 'template.form_id', 0) > 1) {
                $data = [
                    'id' => array_get($event, 'template.linkedForm.uuid'),
                    'cid' => array_get($contact, 'id'),
                    'ticket_id' => array_get($registry, 'tickets.0.id')
                ];
            }
        }
    }

    /**
     * Sends Email to Event Manager
     * @param type $event
     * @param type $registry
     */
    public static function sendEmailToManager($event, $registry, $subject = 'Event Checkin', $queuedBy = 'events.autocheckin.for.free') {
        $manager = array_get($event, 'template.managers.0');
        $tenant = array_get($registry, 'tenant');
        $contact = array_get($registry, 'contact');

        $ticketsSummary = self::getTicketSummary($registry);
        $total = $registry->tickets()->sum('price');

        $data = [
            'tenant' => $tenant,
            'contact' => $contact,
            'event' => $event,
            'manager' => $manager,
            'tickets_summary' => $ticketsSummary,
            'total' => $total
        ];

        $content = view('emails.send.events.event-signup', $data)->render();
        static::sendEmail($content, $tenant, $manager, $subject, $queuedBy);
    }

    public static function sendTicketsToContact($split, $register, $contact, $queuedBy = 'events.purchase.ticket.free')
    {
        $ticketsSummary = self::getTicketSummary($register);
        $total = $register->tickets()->sum('price');

        $params = [
            'tenant' => $register->tenant,
            'contact' => $register->contact,
            'split' => $split,
            'transaction' => null,
            'tickets' => $register->tickets,
            'tickets_summary' => $ticketsSummary,
            'total' => $total
        ];

        $content = view('emails.send.events.ticket', $params)->render();
        static::sendEmail($content, $register->tenant, $register->contact, 'Event ticket', $queuedBy);
    }

    /**
     * Stores tickets and may autocheckin or not
     * @param Integer $id
     * @param Request $request
     * @param Boolean $checkin
     * @param EventRegister $aregistry
     */
    public static function buyTickets($id, $request, $checkin = false, $aregistry = null) {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        $contact = Contact::find(array_get($request, 'id', array_get($request, 'contact_id')));

        $registry = is_null($aregistry) ? static::setRegistry($event, $contact) : $aregistry;
        $amounts = array_get($request, 'ticket_amount', []);
        $options = array_get($request, 'ticket_type', []);
        //dd($options, $amounts);

        // add a fake ticket for free events that do not have ticket options attached
        if (empty($amounts)) {
            static::setFakeTicket($event, $registry, $checkin);
        } else {
            foreach ($amounts as $key => $value) {
                if (!is_null($value)) {
                    for ($i = 1; $i <= $value; $i++) {
                        $option = TicketOption::findOrFail($options[$key]);
                        static::setTicket($event, $registry, $option, $checkin);
                    }
                }
            }
        }

        return $registry;
    }

    /**
     * Checks in (all tickets) after tickets were paid
     * @param Integer $id
     * @param EventRegister $aregistry
     * @param Boolean $checkin
     */
    public static function autoCheckInWithPurchasedTickets($id, $aregistry, $checkin = true) {
        if (!is_null($aregistry)) {
            $ids = array_pluck(array_get($aregistry, 'tickets', []), 'id');
            DB::table('purchased_tickets')->whereIn('id', $ids)
                    ->update([
                        'checked_in' => $checkin,
                        'used' => $checkin,
                        'used_at' => $checkin ? Carbon::now() : null
            ]);
        }
    }

    protected static function getTicketSummary($registry) {
        return $registry->tickets()
        ->select([DB::raw('count(id) as tickets'), 'ticket_name', 'price', 'ticket_option_id', DB::raw('count(id)*price as subtotal')])
        ->groupBy('ticket_option_id')
        ->get();
    }

    public static function exportToIcs($from, $to, $calendars)
    {
        $calendar_names = implode('_', Calendar::whereIn('id', $calendars)->get()->pluck('name')->toArray());
        $export_name = "MP_" . $calendar_names;
        $splits = self::get($from, $to, $calendars, false);
        $event_ids = $splits->pluck('calendar_event_template_id');
        $events = CalendarEvent::whereIn('id', $event_ids)->get();
        $ics = new IcsExport($export_name);
        foreach ($splits as $split) {
            $event = $events->where('id', $split->calendar_event_template_id)->first();
            $address = $event->addresses()->first();
            $country = !empty($address) ? \App\Models\Country::find($address->country_id)->name : null;
            $location = !empty($country) ? "$address->mailing_address_1 $address->city $country" : null;
            $description = $event->description . "<br> <p><b>Event Link: </b>" . route('events.share', array_get($split, 'uuid')) . "</p>";
            $repeating = null;
            if ($event->repeat) {
                $repeating = [
                    'repeat_cycle' => $event->repeat_cycle,
                    'repeat_every' => $event->repeat_every,
                    'repeat_occurrences' => $event->repeat_occurrences,
                    'repeat_ends_on' => $event->repeat_ends_on,
                ];
            }
            $start = $split->start_date;
            $end = $split->end_date;
            $ics->add($start, $end, $event->name, $description, $location, $event->is_all_day, array_get($split, 'uuid'), $repeating);
        }
        return $ics->download();
    }
}
