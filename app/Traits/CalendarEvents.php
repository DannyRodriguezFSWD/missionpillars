<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\TicketOption;
use Carbon\Carbon;
use App\Classes\Email\EmailQueue;

/**
 *
 * @author josemiguel
 */
trait CalendarEvents {

    public function event(Request $request, $id = null) {
        if($id){
            $event = CalendarEvent::findOrFail($id);
        }
        else{
            $event = new CalendarEvent();
            array_set($event, 'uuid', \Ramsey\Uuid\Uuid::uuid1());
        }
        mapModel($event, $request->all());
        array_set($event, 'is_paid', array_get($request, 'is_paid', false));
        array_set($event, 'tax_deductible', array_get($request, 'tax_deductible'));
        
        $this->eventDuration($event, $request);
        if ($request->has('event_repeats') && (bool) array_get($request, 'event_repeats')) {
            $this->eventHasToRepeat($event, $request);
        }

        return $event;
    }

    public function eventDuration(&$event, Request $request) {
        if ($request->has('all_day') && (bool) array_get($request, 'all_day')) {
            array_set($event, 'start', array_get($request, 'event_date').' 00:00:00');
            array_set($event, 'end', array_get($request, 'event_date').' 23:59:59');
            array_set($event, 'is_all_day', true);
        } else {
            $start_timestamp = strtotime(array_get($request, 'start_date') . ' ' . array_get($request, 'start_time'));
            $end_timestamp = strtotime(array_get($request, 'end_date') . ' ' . array_get($request, 'end_time'));
            $start = Carbon::createFromTimestamp($start_timestamp)->toDateTimeString();
            $end = Carbon::createFromTimestamp($end_timestamp)->toDateTimeString();
            array_set($event, 'start', $start);
            array_set($event, 'end', $end);
        }
    }

    public function eventHasToRepeat(&$event, Request $request) {
        $repeatEndsOn = strtolower(array_get($request, 'ends_on'));
        array_set($event, 'repeat', true);
        array_set($event, 'repeat_every', array_get($request, 'repeat_every'));
        array_set($event, 'repeat_cycle', array_get($request, 'repeat_cycle'));
        array_set($event, 'repeat_ends', $repeatEndsOn);

        if ($repeatEndsOn === 'after') {
            array_set($event, 'repeat_occurrences', array_get($request, 'ends_on_occurrences'));
        } else if ($repeatEndsOn === 'on') {
            array_set($event, 'repeat_ends_on', array_get($request, 'ends_on_date'));
        }
    }

    public function setRepeatedEvent($event) {
        $start = Carbon::createFromTimestamp( strtotime(array_get($event, 'start')) );
        $year = Carbon::createFromTimestamp( strtotime(array_get($event, 'start')) )->lastOfYear();
        
        if ($event->repetitions->count() <= 0) {
            
            switch (strtolower($event->repeat_cycle)) {
                case 'daily':
                    $times = floor($year->diffInDays($start) / (int) $event->repeat_every);
                    break;
                case 'weekly':
                    $times = floor($year->diffInWeeks($start) / (int) $event->repeat_every);
                    break;
                case 'monthly':
                    $times = floor($year->diffInMonths($start) / (int) $event->repeat_every);
                    break;
                case 'yearly':
                    $times = floor($year->diffInYears($start) / (int) $event->repeat_every);
                    break;
                default:
                    break;
            }
            $this->repeatedEvent($event, $times);
        }
    }

    public function repeatedEvent($event, $times){
        $start = Carbon::createFromTimestamp( strtotime(array_get($event, 'start')) );
        $end = Carbon::createFromTimestamp( strtotime(array_get($event, 'end')) );
        
        for ($i = 1; $i <= $times; $i++) {
            if(array_get($event, 'repeat_ends') === 'after' && (int) array_get($event, 'repeat_occurrences') === $i ){
                break;
            }
            
            if(array_get($event, 'repeat_ends') === 'on' && !is_null(array_get($event, 'repeat_ends_on')) ){
                $on = Carbon::createFromTimestamp( strtotime(array_get($event, 'repeat_ends_on')) );
                if( $start->gte($on) ){
                    break;
                }
            }
            
            switch ( strtolower($event->repeat_cycle) ) {
                case 'daily':
                    $start->addDays((int) $event->repeat_every);
                    $end->addDays((int) $event->repeat_every);
                    break;
                case 'weekly':
                    $start->addWeeks((int) $event->repeat_every);
                    $end->addWeeks((int) $event->repeat_every);
                    break;
                case 'monthly':
                    $start->addMonths((int) $event->repeat_every);
                    $end->addMonths((int) $event->repeat_every);
                    break;
                case 'yearly':
                    $start->addYears((int) $event->repeat_every);
                    $end->addYears((int) $event->repeat_every);
                    break;
                default :
                    break;
            }
            
            $repeatedEvent = mapModel(new CalendarEvent(), $event);
            array_set($repeatedEvent, 'parent_calendar_event_id', $event->id);
            array_set($repeatedEvent, 'start', $start->toDateTimeString());
            array_set($repeatedEvent, 'end', $end->toDateTimeString());
            //if next line its commented, the repeated event will have same uuid 
            //else, it can have a different uuid so, technically will be other event inherited from main event
            //array_set($repeatedEvent, 'uuid', \Ramsey\Uuid\Uuid::uuid4());
            $repeatedEvent->save();
            
            if( $event->check_in === 'Tags' ){
                $tags = collect($event->tags)->map(function($tag){
                    return array_get($tag, 'id');
                }, []);
                
                $repeatedEvent->tags()->sync($tags);
            }
            else if( $event->check_in === 'Forms' ){
                $forms = collect($event->forms)->map(function($form){
                    return array_get($form, 'id');
                }, []);
                $repeatedEvent->forms()->sync($forms);
            }
        }
    }

    public function setTicketOptions($event, $request){
        $records = array_get($request, 'ticket_record', []);
        $current = array_pluck($event->ticketOptions, 'id');
        $stay = array_intersect($current, $records);
        $delete = array_diff($current, $stay);
        
        TicketOption::destroy($delete);
        if (array_get($event, 'is_paid')) {
            $options = array_get($request, 'ticket_name');
            $prices = array_get($request, 'ticket_price');
        }
        else{
            $options = ['Free Ticket'];
            $prices = [0];
        }
        //dd($records, $current, $stay, $delete);
        foreach($options as $key => $value){
            if( count($records) > 0 && in_array($records[$key], $stay) ){
                $ticket = TicketOption::findOrFail($records[$key]);
            }
            else{
                $ticket = new TicketOption();
            }
            
            array_set($ticket, 'tenant_id', array_get($event, 'tenant_id'));            
            array_set($ticket, 'name', $value);
            array_set($ticket, 'price', $prices[$key]);
            
            if( count($records) > 0 && in_array($records[$key], $stay) ){
                $ticket->update();
            }
            else{
                $event->ticketOptions()->save($ticket);
            }
        }
    }
    
    public function sendEmail($content, $tenant, $contact, $subject = 'Event ticket', $queuedBy = 'events.send.email') {
        EmailQueue::set($contact, [
            'from_name' => array_get($tenant, 'organization'),
            'from_email' => array_get($tenant, 'email'),
            'subject' => $subject,
            'content' => $content,
            'model' => $contact,
            'queued_by' => $queuedBy
        ]);
    }

}
