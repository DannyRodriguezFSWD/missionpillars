<?php

namespace App\Http\Controllers;

use App\Classes\Events\EventSignin;
use App\Constants;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\Contact;
use App\Models\EventRegister;
use App\Models\Group;
use App\Models\PurchasedTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CheckinController extends Controller
{
    const PERMISSION = 'crm-events';
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->tenant->can(self::PERMISSION)) {
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($groupUuid = null, $eventUuid = null)
    {
        if (!(auth()->user()->can('events-view') && auth()->user()->can('group-view'))) {
            abort(404);
        }
        
        $groups = Group::orderBy('name')->get();
        $events = CalendarEventTemplateSplit::whereHas('template')->where('start_date', '>', date('Y-m-d', strtotime(' -2 months')))
                ->where('start_date', '<', date('Y-m-d', strtotime(' +2 months')))
                ->with('template')->orderBy('start_date', 'desc')->get();

        $selectedGroup = $groupUuid ? Group::where('uuid', $groupUuid)->firstOrFail() : null;
        $selectedEvent = $eventUuid ? CalendarEventTemplateSplit::where('uuid', $eventUuid)->firstOrFail() : null;
        
        return view('checkin.index', compact('groups', 'events', 'selectedGroup', 'selectedEvent', 'groupUuid'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!(auth()->user()->can('events-view') && auth()->user()->can('group-view'))) {
            abort(404);
        }
        
        $contact = Contact::findOrFail(array_get($request, 'contact'));
        $split = CalendarEventTemplateSplit::where('uuid', array_get($request, 'event'))->firstOrFail();
        $event = array_get($split, 'template');
        $time = null;
        
        if (array_get($request, 'action') === 'add') {
            $registry = $contact->eventRegistered()->whereHas('event', function ($query) use ($split) {
                $query->where('id', array_get($split, 'id'));
            })->first();
            
            if ($registry) {
                $ticket = $registry->tickets()->firstOrFail();
            } else {
                $registry = EventSignin::setRegistry($split, $contact);
                $ticket = EventSignin::setFakeTicket($split, $registry);
            }
            
            array_set($ticket, 'temporary_hold', 0);
            array_set($ticket, 'temporary_hold_ends_at', null);
            array_set($ticket, 'checked_in', true);
            array_set($ticket, 'checked_in_time', Carbon::now());
            array_set($ticket, 'checked_out_time', null);
            array_set($ticket, 'used', true);
            array_set($ticket, 'used_at', Carbon::now());
            $ticket->update();
            $time = '<small class="text-muted checkinTime">'.displayLocalDateTime(Carbon::now())->format('H:i').'</small>';
        } elseif (array_get($request, 'action') === 'checkout') {
            $ticket = $contact->checkedIn()->whereHas('event', function ($query) use ($split) {
                $query->where('id', array_get($split, 'id'));
            })->firstOrFail()->tickets()->firstOrFail();
            array_set($ticket, 'checked_out_time', Carbon::now());
            $ticket->update();
            if (array_get($ticket, 'checked_in_time')) {
                $time = '<small class="text-muted checkinTime">'.displayLocalDateTime(Carbon::createFromFormat('Y-m-d H:i:s', array_get($ticket, 'checked_in_time')))->format('H:i').' - '.displayLocalDateTime(Carbon::now())->format('H:i').'</small>';
            }
        } elseif (array_get($request, 'action') === 'remove') {
            $ticket = $contact->checkedIn()->whereHas('event', function ($query) use ($split) {
                $query->where('id', array_get($split, 'id'));
            })->firstOrFail()->tickets()->firstOrFail();
            array_set($ticket, 'checked_in', false);
            array_set($ticket, 'checked_in_time', null);
            array_set($ticket, 'checked_out_time', null);
            array_set($ticket, 'printed_tag', 0);
            $ticket->update();
        }

        return response()->json(['success' => true, 'time' => $time]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function report($id, Request $request)
    {
        if (!(auth()->user()->can('events-view') && auth()->user()->can('group-view'))) {
            abort(404);
        }
        
        $group = Group::findOrFail($id);
        $contacts = $group->contacts()->with('checkedIn.event')->orderByDirectorySort()->get();
        
        $fromDate = Carbon::createFromFormat('Y-m-d', array_get($request, 'from_date'))->subDays(6)->endOfWeek()->startOfDay();
        $toDate = Carbon::createFromFormat('Y-m-d',array_get($request, 'to_date'))->endOfWeek()->subDay();
        $diff = $fromDate->diffInDays($toDate);
        
        $reportDates = [];
        
        for ($i = 1; $i <= ceil($diff / 7); $i++) {
            $weekStart = $fromDate->copy()->addDays(($i - 1) * 7);
            $weekEnd = $fromDate->copy()->addDays($i * 7)->subDay();
            
            $reportDates[] = [
                'start' => $weekStart,
                'end' => $weekEnd
            ];
        }
        
        $reportContacts = [];
        $totalAttendance = [];
        
        foreach ($contacts as $contact) {
            $lastAttendance = null;
            $reportContacts[array_get($contact, 'id')] = [
                'weeks_attended' => 0
            ];
            
            foreach (array_get($contact, 'checkedIn') as $checkin) {
                $eventDate = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime(array_get($checkin, 'event.start_date'))));
                
                if ($toDate->gte($eventDate) && (!$lastAttendance || $eventDate->gte($lastAttendance))) {
                    $lastAttendance = $eventDate;
                }
                
                foreach ($reportDates as $date) {
                    if ($eventDate->gte($date['start']) && $date['end']->gte($eventDate)) {
                        if (!isset($reportContacts[array_get($contact, 'id')][$date['start']->format('Y-m-d')])) {
                            $reportContacts[array_get($contact, 'id')][$date['start']->format('Y-m-d')] = 1;
                            $reportContacts[array_get($contact, 'id')]['weeks_attended']++;
                            
                            if (!isset($totalAttendance[$date['start']->format('Y-m-d')])) {
                                $totalAttendance[$date['start']->format('Y-m-d')] = 1;
                            } else {
                                $totalAttendance[$date['start']->format('Y-m-d')]++;
                            }
                            
                            break;
                        }
                    }
                }
            }
            
            $reportContacts[array_get($contact, 'id')]['first_name'] = array_get($contact, 'first_name');
            $reportContacts[array_get($contact, 'id')]['last_name'] = array_get($contact, 'last_name');
            $reportContacts[array_get($contact, 'id')]['last_attendance'] = $lastAttendance;
        }
        
        $tail = str_replace(':', '', displayLocalDateTime(Carbon::now()->toDateTimeString())->toDateTimeString());
        $tail = str_replace('-', '', $tail);
        $tail = str_replace(' ', '-', $tail);
        $filename = substr(implode('-', [array_get($group, 'name'), $tail]), 0, 28);
        
        $excelData = [
            'filename' => $filename,
            'reportContacts' => $reportContacts,
            'reportDates' => $reportDates,
            'group' => $group,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalAttendance' => $totalAttendance
        ];
        
        Excel::create($filename, function($excel) use ($excelData) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($excelData) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('checkin.includes.attendance-report', $excelData);
            });
        })->download('xlsx');
    }
    
    public function print(Request $request)
    {
        $search = array_get($request, 'search');
        $searchParam = '%' . $search . '%';
        $eventUuid = array_get($request, 'event');
        $groupUuid = array_get($request, 'group');
        $event = CalendarEventTemplateSplit::where('uuid', $eventUuid)->firstOrFail();
        
        $contacts = Contact::whereRaw("CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,''), ' ', IFNULL(email_1,''), IFNULL(company, '')) like ?", [$searchParam])
        ->whereHas('checkedIn.event', function ($query) use ($eventUuid) {
            $query->where('uuid', $eventUuid);
        });
        
        if ($groupUuid) {
            $contacts->whereHas('groups', function ($query) use ($groupUuid) {
                $query->where('uuid', $groupUuid);
            });
        }
        
        $contactIds = $contacts->get()->pluck('id')->toArray();
        
        $checkinIds = EventRegister::where('calendar_event_template_split_id', array_get($event, 'id'))->whereIn('contact_id', $contactIds)->get()->pluck('id')->toArray();

        $tickets = PurchasedTicket::whereIn('calendar_event_contact_register_id', $checkinIds)->where('printed_tag', 0)->get();
        
        if ($tickets) {
            foreach ($tickets as $ticket) {
                $ticket->printed_tag = 1;
                $ticket->update();
            }
        }
        
        return response()->json(['success' => true]);
    }
    
    public function rePrint(Request $request)
    {
        $event = CalendarEventTemplateSplit::where('uuid', array_get($request, 'event'))->firstOrFail();
        $checkin = EventRegister::where('calendar_event_template_split_id', array_get($event, 'id'))->where('contact_id', array_get($request, 'contactId'))->firstOrFail();
        $ticket = PurchasedTicket::where('calendar_event_contact_register_id', array_get($checkin, 'id'))->firstOrFail();
        
        $ticket->printed_tag = 0;
        $ticket->update();
        
        return response()->json(['success' => true]);
    }
}
