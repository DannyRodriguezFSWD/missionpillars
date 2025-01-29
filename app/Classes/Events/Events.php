<?php

namespace App\Classes\Events;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\TicketOption;
use App\Classes\Email\EmailQueue;
use App\Models\Purpose;

/**
 * Description of Events
 *
 * @author josemiguel
 */
class Events {

    /**
     * Creates or updates an event template
     * @param mixed $request
     * @param int $id
     * @return CalendarEvent
     */
    public static function template($request, $id = null) {
        if (!is_null($id)) {
            $split = CalendarEventTemplateSplit::findOrFail($id);
            $event = array_get($split, 'template');

            array_set($event, 'allow_auto_check_in', null);
            array_set($event, 'allow_reserve_tickets', null);
            array_set($event, 'is_all_day', 0);
            array_set($event, 'pay_later', 0);
            
            mapModel($event, $request->all());

            if( is_null(array_get($request, 'event_repeats')) ){
                array_set($event, 'repeat_cycle', null);
                array_set($event, 'repeat_every', null);
                array_set($event, 'repeat_ends_on', null);
                array_set($event, 'repeat_occurrences', null);
                array_set($event, 'repeat_ends', null);
            }

        } else {
            $event = new CalendarEvent();
            if( !is_null(array_get($request, 'event_repeats')) ){
                mapModel($event, $request->all());
            }
            else{
                mapModel($event, $request->except([
                    'repeat_cycle',
                    'repeat_every',
                    'ends_on',
                    'ends_on_occurrences',
                    'ends_on_date',
                    'content'
                ]));
            }
        }

        if((int)array_get($request, 'purpose_id') <= 1){
            $purpose = Purpose::orderBy('id')->first();
            array_set($event, 'purpose_id', array_get($purpose, 'id'));
        }

        array_set($event, 'check_in', 'Everyone'); //remove when check in limitation its ready
        array_set($event, 'is_paid', array_get($request, 'is_paid', false));
        array_set($event, 'tax_deductible', array_get($request, 'tax_deductible'));

        //set timeframe depending on if it is all day or not
        if ($request->has('all_day') && (bool) array_get($request, 'all_day')) {
            array_set($event, 'start', array_get($request, 'event_date') . ' 00:00:00');
            array_set($event, 'end', array_get($request, 'event_date') . ' 23:59:59');
            array_set($event, 'is_all_day', true);
        } else {
            $start_timestamp = strtotime(array_get($request, 'start_date') . ' ' . array_get($request, 'start_time'));
            $end_timestamp = strtotime(array_get($request, 'end_date') . ' ' . array_get($request, 'end_time'));
            $start_parsed = Carbon::createFromTimestamp($start_timestamp)->toDateTimeString();
            $end_parsed = Carbon::createFromTimestamp($end_timestamp)->toDateTimeString();

            $start = setUTCDateTime($start_parsed, $request->timezone);
            $end = setUTCDateTime($end_parsed, $request->timezone);
            array_set($event, 'start', $start);
            array_set($event, 'end', $end);
        }

        //if it is a recurring event
        if ($request->has('event_repeats') && (bool) array_get($request, 'event_repeats')) {
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
        array_set($event, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
        array_set($event, 'description', array_get($request, 'content'));
        $event->custom_header = strip_tags($request->custom_header) ? $request->custom_header : null;

        if (array_get($event, 'group_id') === 'all') {
            array_set($event, 'group_id', null);
        }
        
        if ($request->has('remind_manager') && (bool) array_get($request, 'remind_manager')) {
            array_set($event, 'remind_manager', true);
        } else {
            array_set($event, 'remind_manager', false);
        }
        
        if (!is_null($id)) {
            $event->update();
        } else {
            $event->save();
        }
        return $event;
    }

    public static function splits($event, $start = null, $id = null, $end = null) {
        //dd($start, $end);
        if (array_get($event, 'repeat')) {
            self::repeat($event, $start, $end);
        } else {
            self::split($event, array_get($event, 'start'), array_get($event, 'end'), $id);
        }
    }

    public static function split($event, $start, $end, $id = null) {
        $split = is_null($id) ? new CalendarEventTemplateSplit() : CalendarEventTemplateSplit::findOrFail($id);
        array_set($split, 'calendar_event_template_id', array_get($event, 'id'));
        array_set($split, 'start_date', $start);
        array_set($split, 'end_date', $end);
        if (is_null($id)) {
            array_set($split, 'uuid', Uuid::uuid4());
        }
        array_set($split, 'tenant_id', array_get($event, 'tenant_id'));

        if (is_null($id)) {
            $event->splits()->save($split);
        } else {
            $split->update();
        }

        return $split;
    }

    public static function incrementDate(&$date, $cycle = 'daily', $increment = 0) {
        switch (strtolower($cycle)) {
            case 'daily':
                $date->addDays($increment);
                break;
            case 'weekly':
                $date->addWeeks($increment);
                break;
            case 'monthly':
                $date->addMonths($increment);
                break;
            case 'yearly':
                $date->addYears($increment);
                break;
            default :
                break;
        }
    }

    public static function repeatByOccurrences($event, $astart, $aend) {
        $from = Carbon::parse($astart)->startOfDay();
        $to = Carbon::parse($aend)->startOfDay();
        $repeat = (int) array_get($event, 'repeat_occurrences') - 1;
        //$event_finish = Carbon::parse(array_get($event, 'start'))->addDays($repeat);
        $event_finish = Carbon::parse(array_get($event, 'start'));
        self::incrementDate($event_finish, array_get($event, 'repeat_cycle'), $repeat);

        $splits = $event->splits()
                ->whereBetween('start_date', [$from, $to])
                ->orderBy('id', 'desc')
                ->get();

        $last = $event->splits()->orderBy('id', 'desc')->first();
        if(is_null($last) ){
            $start = Carbon::parse(array_get($event, 'start'));
            $end = Carbon::parse(array_get($event, 'end'));
        }
        else{
            $start = Carbon::parse(array_get($last, 'start_date'))->addDay();
            $end = Carbon::parse(array_get($last, 'end_date'))->addDay();
        }

        $currentDate = $start->copy();

        //dd('current', $currentDate, 'finish', $event_finish, $currentDate->lte($event_finish));

        while($currentDate->lte($event_finish)){
            self::split($event, $start, $end);
            self::incrementDate($start, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($end, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($currentDate, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
        }

        return $splits;
    }





    public static function repeatByEndDate($event, $astart, $aend) {
        $last = $event->splits()->orderBy('id', 'desc')->first();
        if (is_null($last)) {
            $event_starts = Carbon::parse(array_get($event, 'start'));
            $event_ends = Carbon::parse(array_get($event, 'end'));
        } else {
            $event_starts = Carbon::parse(array_get($last, 'start_date'))->addDay();
            $event_ends = Carbon::parse(array_get($last, 'end_date'))->addDay();
        }
        $to = Carbon::parse(array_get($event, 'repeat_ends_on'))->endOfDay();
        $currentDate = $event_starts->copy();

        while ($currentDate->lte($to)) {
            self::split($event, $event_starts, $event_ends);
            self::incrementDate($event_ends, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($event_starts, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($currentDate, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
        }

        return;
    }

    public static function repeatForever($event, $astart, $aend) {
        $to = Carbon::parse($aend)->endOfDay();
        $last = $event->splits()->orderBy('id', 'desc')->first();

        if (is_null($last)) {
            $event_starts = Carbon::parse(array_get($event, 'start'));
            $event_ends = Carbon::parse(array_get($event, 'end'));
        } else {
            $event_starts = Carbon::parse(array_get($last, 'start_date'));
            $event_ends = Carbon::parse(array_get($last, 'end_date'));
            self::incrementDate($event_starts, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
        }

        $currentDate = $event_starts->copy();

        while ($currentDate->lt($to)) {
            self::split($event, $event_starts, $event_ends);
            self::incrementDate($event_starts, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($event_ends, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
            self::incrementDate($currentDate, array_get($event, 'repeat_cycle'), array_get($event, 'repeat_every', 0));
        }

        return;
    }

    public static function repeat($event, $start = null, $end = null) {
        if (strtolower(array_get($event, 'repeat_ends')) === 'after') {
            return self::repeatByOccurrences($event, $start, $end);
        }

        if (strtolower(array_get($event, 'repeat_ends')) === 'on') {
            return self::repeatByEndDate($event, $start, $end);
        }

        if (strtolower(array_get($event, 'repeat_ends')) === 'never') {
            return self::repeatForever($event, $start, $end);
        }
    }

    public static function get($from, $to, $calendar = 0, $format = true) {
        if (is_array($calendar)){
            $splits = CalendarEventTemplateSplit::whereHas('template', function ($query) use ($calendar) {
                $query->whereIn('calendar_id', $calendar);
            })->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])->orWhere(function ($query) use ($from, $to) {
                    $query->where('start_date', '<=', $to)->where('end_date', '>=', $from);
                });
            })->get();
        }
        elseif ((int) $calendar === 0) {
            $splits = CalendarEventTemplateSplit::whereHas('template')->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])->orWhere(function ($query) use ($from, $to) {
                    $query->where('start_date', '<=', $to)->where('end_date', '>=', $from);
                });
            })->get();
        } else {
            $splits = CalendarEventTemplateSplit::whereHas('template', function ($query) use ($calendar) {
                $query->where('calendar_id', $calendar);
            })->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])->orWhere(function ($query) use ($from, $to) {
                    $query->where('start_date', '<=', $to)->where('end_date', '>=', $from);
                });
            })->get();
        }
        if (!$format) return $splits;
        return self::formatCalendarEvents($splits);
    }

    public static function getPublic($from, $to, $calendars = [], $calendar = 0) {
        if ((int) $calendar === 0) {
            $splits = CalendarEventTemplateSplit::whereHas('template', function ($query) use ($calendars) {
                $query->whereHas('calendar', function ($q) {
                    $q->where('public', true);
                })->whereIn('calendar_id', $calendars);
            })->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])->orWhere(function ($query) use ($from, $to) {
                    $query->where('start_date', '<=', $to)->where('end_date', '>=', $from);
                });
            })->get();
        } else {
            $splits = CalendarEventTemplateSplit::whereHas('template', function ($query) use ($calendar) {
                $query->whereHas('calendar', function ($q) {
                    $q->where('public', true);
                })->where('calendar_id', $calendar);
            })->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])->orWhere(function ($query) use ($from, $to) {
                    $query->where('start_date', '<=', $to)->where('end_date', '>=', $from);
                });
            })->get();
        }

        $events = self::formatCalendarEvents($splits);

        return $events;
    }

    public static function formatCalendarEvents($splits) {
        $events = collect($splits)->reduce(function($carry, $item) {
            $event['id'] = array_get($item, 'id');
            $event['allDay'] = (bool) array_get($item, 'template.is_all_day');
            $event['title'] = array_get($item, 'template.name');

            if(array_get($item, 'template.is_all_day')){
                $event['start'] = Carbon::parse(array_get($item, 'start_date'))->toDateString();
                $event['end'] = Carbon::parse(array_get($item, 'end_date'))->toDateString();
            }
            else{
                $event['start'] = displayLocalDateTime(array_get($item, 'start_date'), array_get($item, 'template.timezone'))->toDateTimeString();
                $event['end'] = displayLocalDateTime(array_get($item, 'end_date'), array_get($item, 'template.timezone'))->toDateTimeString();
            }

            $event['color'] = array_get($item, 'template.calendar.color');
            $event['description'] = array_get($item, 'template.description');
            $event['timezone'] = array_get($item, 'template.timezone');
            array_push($carry, $event);
            return $carry;
        }, []);

        return $events;
    }

    public static function setTicketOptions($event, $request) {
        //dd($request->all());
        $records = array_get($request, 'ticket_record', []);
        $current = array_pluck($event->ticketOptions, 'id');
        $stay = array_intersect($current, $records);
        $delete = array_diff($current, $stay);

        //do not delete tickets, just hide them
        //so all previous purchased ticket will still work
        TicketOption::whereIn('id', $delete)->update([
            'show_ticket' => false
        ]);

        $options = array_get($request, 'ticket_name', []);
        $prices = array_get($request, 'ticket_price', []);
        $availability = array_get($request, 'ticket_availability', []);
        $is_free_ticket = array_get($request, 'is_free_ticket', []);
        $allow_unlimited_tickets = array_get($request, 'allow_unlimited_tickets', []);

        foreach ($options as $key => $value) {
            $ticket = null;
            $new_ticket = false;
            if( isset($records[$key]) ){
                $ticket = TicketOption::find($records[$key]);
            }

            if(is_null($ticket) ){
                $ticket = new TicketOption();
                $new_ticket = true;
            }

            array_set($ticket, 'tenant_id', array_get($event, 'tenant_id'));
            array_set($ticket, 'name', $value);
            array_set($ticket, 'price', array_get($prices, $key, 0));
            array_set($ticket, 'availability', array_get($availability, $key, 0));
            array_set($ticket, 'is_free_ticket', array_get($is_free_ticket, $key, false));
            array_set($ticket, 'allow_unlimited_tickets', array_get($allow_unlimited_tickets, $key, false));

            if (!$new_ticket) {
                $ticket->update();
            } else {
                $event->ticketOptions()->save($ticket);
            }
        }
    }

    public static function sendEmail($content, $tenant, $contact, $subject = 'Event ticket', $queuedBy = 'events.send.email') {
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
