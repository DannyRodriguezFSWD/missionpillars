<?php

namespace App\Http\Controllers;

use App\Policies\CalendarEventPolicy;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\Calendar;
use App\Constants;
use App\Models\Address;
use App\Models\Folder;
use App\Traits\TagsTrait;
use App\Models\Form;
use App\Models\Contact;
use App\Classes\ApiJsonResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\CountriesTrait;
use App\Models\Purpose;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use App\Classes\Subdomains\TenantSubdomain;
use App\Http\Requests\Events\SigninEvent;
use App\Http\Requests\Events\StoreEvent;
use App\Models\Group;

use Carbon\Carbon;
use App\Models\EventRegister;
use App\Models\PurchasedTicket;
use App\Models\TicketOption;
use App\Models\AltId;
use App\Models\TransactionTemplate;
use App\Classes\Events\EventSignin as CalendarEvents;
use App\Models\CalendarEventTemplateSplit;

use App\Classes\Events\EventSignin;
use App\Classes\Redirections;

use Barryvdh\DomPDF\Facade as PDF;

class CalendarEventsController extends Controller {

    use TagsTrait,
        CountriesTrait;

    const PERMISSION = 'crm-events';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(auth()->check()){
                if(!auth()->user()->tenant->can(self::PERMISSION)){
                    return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
                }
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $this->authorize('viewAll', CalendarEventTemplateSplit::class);
        $db = Calendar::all();
        if (count($db) <= 0) {
            return redirect()->route('calendars.index', ['action' => 'create_default_calendar']);
        }

        $mainCalendar = Calendar::where('name', 'Main Calendar')->first();
        $calendars = Calendar::where('name', '!=', 'Main Calendar')->orderBy('name')->get();
        $calendars->prepend($mainCalendar);
        
        $chart = Purpose::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        $form = Form::whereNull('tenant_id')->orderBy('id', 'asc')->first();

        $dropDownForms = collect(Form::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($form, 'id', 1) => array_get($form, 'name', 'None')]);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($chart, 'id', 1) => 'None']);

        $events = CalendarEvents::get(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        $countries = $this->getCountries();
        $forms = Form::whereNotNull('tenant_id')->get();
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'calendars', $calendars);
        array_set($data, 'events', json_encode($events));
        array_set($data, 'forms', $forms);
        array_set($data, 'dropDownForms', $dropDownForms);
        array_set($data, 'countries', $countries);
        array_set($data, 'campaigns', $campaigns);
        array_set($data, 'charts', $charts);
        array_set($data, 'public', false);

        if (array_has($request, 'group')) {
            $group = Group::findOrFail(array_get($request, 'group'));
            array_set($data, 'calendar_id', array_get($group, 'calendar.id'));
        }

        return view('events.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $this->authorize('create',CalendarEvent::class);
        $timezones = getAvalableTimezones();

        $mainCalendar = Calendar::where('name', 'Main Calendar')->first();
        $calendars = Calendar::where('name', '!=', 'Main Calendar')->orderBy('name')->get();
        $calendars->prepend($mainCalendar);
        
        $chart = Purpose::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        $form = Form::whereNull('tenant_id')->orderBy('id', 'asc')->first();

        $dropDownForms = collect(Form::whereNotNull('tenant_id')->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($form, 'id', 1) => array_get($form, 'name', 'None')]);

        $campaigns = collect(Campaign::orgOwned()->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::whereNotNull('tenant_id')->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($chart, 'id', 1) => 'None']);

        $db = CalendarEvent::all();

        $events = collect($db)->reduce(function($events, $db) {
            $event['id'] = array_get($db, 'id');
            $event['allDay'] = (bool) array_get($db, 'is_all_day');
            $event['title'] = array_get($db, 'name');
            $event['start'] = array_get($db, 'start');
            $event['end'] = array_get($db, 'end');
            $event['color'] = array_get($db, 'calendar.color');
            $event['description'] = array_get($db, 'description');
            array_push($events, $event);
            return $events;
        }, []);

        $countries = $this->getCountries();
        $forms = Form::whereNotNull('tenant_id')->get();

        $defaultdate = array_get($request, 'date')?:date('Y-m-d');
        $data = [];
        array_set($data, 'calendars', $calendars);
        array_set($data, 'events', json_encode($events));
        array_set($data, 'forms', $forms);
        array_set($data, 'dropDownForms', $dropDownForms);
        array_set($data, 'countries', $countries);
        array_set($data, 'campaigns', $campaigns);
        array_set($data, 'charts', $charts);
        array_set($data, 'event', null);
        array_set($data, 'manager', null);
        array_set($data, 'date', array_get($request, 'date', date('Y-m-d')));
        array_set($data, 'timezones', $timezones);
        array_set($data, 'split', null);
        array_set($data, 'start_date', $defaultdate);
        array_set($data, 'start_time', null);
        array_set($data, 'end_date', $defaultdate);
        array_set($data, 'end_time', null);

        $groupId = array_get($request, 'group');
        array_set($data, 'groupId', $groupId);
        $allGroups = Group::orderBy('name')->get();
        $groups = ['all' => 'All People'];
        foreach ($allGroups as $gr) {
            $groups[array_get($gr, 'id')] = array_get($gr, 'name');
        }
        array_set($data, 'groups', $groups);
        if ($groupId) {
            $group = Group::find($groupId);
            $manager = array_get($group, 'manager');
            array_set($manager, 'name', array_get($manager, 'full_name_email'));
            array_set($data, 'manager', $manager);
            array_set($data, 'groupEvent', true);
            array_set($data, 'calendar_id', array_get($group, 'calendar_id'));
        }
        
        return view('events.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEvent $request) 
    {
        $this->authorize('create',CalendarEvent::class);
        
        $event = CalendarEvents::template($request);

        if (is_null($event)) abort(500);

        if (array_get($event, 'repeat')) {
            //create its splits
            //CalendarEvents::splits($event, array_get($event, 'start'));
        }
        else{
            CalendarEvents::split($event, array_get($event, 'start'), array_get($event, 'end'));
        }
        $event->managers()->sync(array_get($request, 'contact_id'));
        CalendarEvents::setTicketOptions($event, $request);

        if ($request->has('tags')) {
            $event->tags()->sync(array_get($request, 'tags'), false);
        }

        if ($request->has('forms')) {
            $event->forms()->sync(array_get($request, 'forms'), false);
        }

        $address = $event->addressInstance->first();
        if ($address) {
            mapModel($address, $request->all());
            $address->update();
        } else if ($request->has('mailing_address_1') && !is_null(array_get($request, 'mailing_address_1'))) {
            $address = mapModel(new Address(), $request->all());
            array_set($address, 'relation_id', array_get($event, 'id'));
            array_set($address, 'relation_type', get_class($event));
            auth()->user()->tenant->addresses()->save($address);
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $file->store('public/event_images');
            array_set($event, 'img_cover', $file->hashName());
            $event->update();
        }
        return response()->json(['id' => array_get($event->splits()->first(), 'id'), 'uuid' => array_get($event->splits()->first(), 'uuid'), 'message' => 'Event Created!', 'redirect' => route('events.settings', ['id' => array_get($event->splits()->first(), 'id')])]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $split = CalendarEventTemplateSplit::whereHas('template')->findOrFail($id);
        $this->authorize('show',$split);
        $event = array_get($split, 'template');
        $ids = array_pluck(array_get($split, 'registries'), 'contact_id');
        $gender = DB::table('contacts')
                ->select(DB::raw('count(*) as total, gender as label'))
                ->whereIn('id', $ids)
                ->orderBy('gender', 'asc')
                ->groupBy('gender')
                ->get();
        $gChart = [
            'total' => json_encode(array_pluck($gender, 'total')),
            'labels' => json_encode(array_pluck($gender, 'label')),
            'table' => $gender
        ];

        $status = DB::table('contacts')
                ->select(DB::raw('count(*) as total, marital_status as label'))
                ->whereIn('id', $ids)
                ->orderBy('marital_status', 'asc')
                ->groupBy('marital_status')
                ->get();
        $sChart = [
            'total' => json_encode(array_pluck($status, 'total')),
            'labels' => json_encode(array_pluck($status, 'label')),
            'table' => $status
        ];

        $ages = DB::table('contacts')
                ->select(DB::raw('count(*) as total, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age'))
                ->whereIn('id', $ids)
                ->groupBy(DB::raw(
                                'if(age IS NULL, 0, 1),
                        if(age between 0 and 10, 0, 1),
                        if(age between 11 and 20, 0, 1),
                        if(age between 21 and 30, 0, 1),
                        if(age between 31 and 40, 0, 1),
                        if(age between 41 and 50, 0, 1)'
                ))
                ->get();
        $aChart = [
            'total' => json_encode(array_pluck($ages, 'total')),
            'labels' => json_encode($this->ranges($ages)),
            'table' => $this->ranges($ages),
            'values' => array_pluck($ages, 'total')
        ];

        $data = [
            'event' => $event,
            'total' => count($ids),
            'gender' => $gChart,
            'status' => $sChart,
            'age' => $aChart,
            'split' => $split
        ];
        return view('events.show')->with($data);
    }

    private function ranges($ages) {
        $rages = [];
        foreach ($ages as $value) {
            if ($value->age === null) {
                array_push($rages, 'Unspecified');
            } else if ($value->age <= 10) {
                array_push($rages, '<=10');
            } else if ($value->age >= 11 && $value->age <= 20) {
                array_push($rages, '11-20');
            } else if ($value->age >= 21 && $value->age <= 30) {
                array_push($rages, '21-30');
            } else if ($value->age >= 31 && $value->age <= 40) {
                array_push($rages, '31-40');
            } else if ($value->age >= 41 && $value->age <= 50) {
                array_push($rages, '41-50');
            } else {
                array_push($rages, '> 50');
            }
        }
        return $rages;
    }

    /**
     * Show the form for editing the specified resource.
     * Not used... see settings
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id) {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreEvent $request, $id) 
    {
        $split = CalendarEventTemplateSplit::findOrFail($id);
        $this->authorize('update',$split);
        if ((bool) array_get($request, 'rescheduled') === true) {
            if( is_null(array_get($request, 'event_repeats')) ){
                $template = array_get($split, 'template');
                $splits = $template->splits()->where([
                            ['id', '>=', array_get($split, 'id')]
                        ])->get();
                CalendarEventTemplateSplit::destroy(array_pluck($splits, 'id'));

                $last = $template->splits()->orderBy('id', 'desc')->first();
                array_set($template, 'repeat_ends', 'On');
                array_set($template, 'repeat_ends_on', array_get($last, 'start_date', array_get($template, 'start')));
                $template->update();

                $event = CalendarEvents::template($request);
            }
            else{
                $event = CalendarEvents::template($request, $id);
            }

            CalendarEvents::splits($event);
        } else {
            $event = CalendarEvents::template($request, $id);
            CalendarEvents::splits($event, array_get($event, 'start'), $id);
        }

        CalendarEvents::setTicketOptions($event, $request);
        if (!array_has($request, 'form_must_be_filled')) {
            array_set($event, 'form_must_be_filled', false);
        }
        $event->update();

        $event->managers()->sync(array_get($request, 'contact_id'));

        if ($request->has('removeCoverImage')) {
            checkAndDeleteFile(storage_path('app/public/event_images/' . $event->img_cover));
            $event->update(['img_cover' => null]);
        }

        if ($request->hasFile('image') && $request->file('image')->isValid() && !$request->has('removeCoverImage')) {
            if (!empty($event->img_cover)) {
                unlink(storage_path('app/public/event_images/' . $event->img_cover));
            }

            $file = $request->file('image');
            $file->store('public/event_images');
            array_set($event, 'img_cover', $file->hashName());
            $event->update();
        }

        if ($request->has('tags')) {
            $event->tags()->sync(array_get($request, 'tags'), false);
        }

        if ($request->has('forms')) {
            $event->forms()->sync(array_get($request, 'forms'), false);
        }

        $address = $event->addressInstance->first();
        if ($address) {
            mapModel($address, $request->all());
            $address->update();
        } else if ($request->has('mailing_address_1') && !is_null(array_get($request, 'mailing_address_1'))) {
            $address = mapModel(new Address(), $request->all());
            array_set($address, 'relation_id', array_get($event, 'id'));
            array_set($address, 'relation_type', get_class($event));
            auth()->user()->tenant->addresses()->save($address);
        }
        $event->load('splits');

        return response()->json(['message' => 'Event Successfully Updated!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        $this->authorize('delete',$event);
        if ($event->delete()) {
            return redirect()->route('events.index')->with('message', __('Event successfully deleted'));
        }
        abort(500);
    }

    public function attenders($id, Request $request) {
        $split = CalendarEventTemplateSplit::findOrFail($id);
        $this->authorize('show',$split);
        $event = array_get($split, 'template');
        $contacts = $split->contacts;
        if ($event->linkedForm) {
            $eventFormEntries = $event->linkedForm->entries()->where([
                ['relation_type', '=', CalendarEventTemplateSplit::class],
                ['relation_id', '=', array_get($split, 'id')]
            ])
            ->get();
        }

        $attendersWithEventFormEntry = $event->linkedForm ? $eventFormEntries->pluck('contact_id')->toArray() : [];
        $attendersWithEventCheckIn = array_pluck($split->contactsCheckedIn, 'registry.contact.id');

        $data = [
            'event' => $event,
            'total' => count($contacts),
            'contacts' => $contacts,
            'attendersWithEventFormEntry' => $attendersWithEventFormEntry,
            'attendersWithEventCheckIn' => $attendersWithEventCheckIn,
            'split' => $split
        ];

        return view('events.attenders')->with($data);
    }

    public function report($id, Request $request) {
        $split = CalendarEventTemplateSplit::findOrFail($id);
        $this->authorize('show',$split);
        $event = array_get($split, 'template');

        //$from = Carbon::parse( array_get($request, 'from', array_get($split, 'start_date')) );
        //$to = Carbon::parse(array_get($request, 'to', array_get($split, 'end_date')));
        //$repetitions = $event->splits()->whereBetween('start_date', [$from, $to])->get();
        $repetitions = $event->splits;
        $contacts = $split->contacts;

        $data = [
            'event' => $event,
            'repetitions' => $repetitions,
            'total' => $split->contactsCheckedIn->count(),
            'contacts' => $contacts,
            'split' => $split
        ];

        return view('events.report')->with($data);
    }

    public function checkin($id, Request $request)
    {
        $split = CalendarEventTemplateSplit::whereHas('template')->findOrFail($id);
        $this->authorize('update', $split);
        $event = array_get($split, 'template');
        $registries = $split->registries->sortByDesc('id')->values()->all();

        $showPaidColumn = false;
        $paidTickets = $split->purchasedTickets()->whereHas('registry', function($query){
            $query->where('paid', true);
        })->count();

        if( array_get($event, 'is_paid') || $paidTickets > 0 ){
            $showPaidColumn = true;
        }
        
        $groups = Group::orderBy('name')->get();

        $reportFileName = auth()->user()->tenant->organization.' - Checkin Report - '.date('F j, 2024');
        
        $data = [
            'event' => $event,
            'registries' => $registries,
            'form' => array_get($event, 'linkedForm'),
            'show_paid_column' => $showPaidColumn,
            'split' => $split,
            'groups' => $groups,
            'reportFileName' => $reportFileName
        ];

        return view('events.mobilecheckin')->with($data);
    }

    public function autocheckin($id, $contact_id, $registry_id, Request $request) {
        $register = EventRegister::findOrFail($registry_id);
        $ticket_id = array_get($request, 't', 0);

        if( (int)$ticket_id === 0 ){
            $ticket = $register->tickets()->first();
        }
        else{
            $ticket = PurchasedTicket::findOrFail($ticket_id);
        }

        if (!is_null($ticket)) {
            array_set($ticket, 'checked_in', true);
            array_set($ticket, 'used', true);
            array_set($ticket, 'used_at', Carbon::now());
            $ticket->update();

            return redirect()->route('events.checkin', ['id' => $id]);
        }
        abort(500);
    }

    public function uncheck($id, $contact, Request $request) {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        if ($request->has('action') && array_get($request, 'action') === 'mobile') {
            try {
                $ticket = PurchasedTicket::findOrFail(array_get($request, 'id'));
                array_set($ticket, 'checked_in', false);
                array_set($ticket, 'used', false);
                array_set($ticket, 'used_at', null);
                $ticket->update();
                $response = new ApiJsonResponse(200);
                return $response->toJson();
            } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
                $response = new ApiJsonResponse(400);
                return $response->toJson();
            }
        }
        $response = new ApiJsonResponse(200);
        return $response->toJson();
    }

    public function checkinContacts($id, Request $request) {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        if ($request->has('action') && array_get($request, 'action') === 'mobile') {
            try {
                $ticket = PurchasedTicket::find(array_get($request, 'id'));

                if ($event->template->is_paid === 0 && empty($ticket)) {
                    $registry = EventRegister::findOrFail(array_get($request, 'registry_id'));
                    $ticket = EventSignin::setFakeTicket($event, $registry);
                    array_set($ticket, 'temporary_hold', 0);
                    array_set($ticket, 'temporary_hold_ends_at', null);
                }

                array_set($ticket, 'checked_in', true);
                array_set($ticket, 'used', true);
                array_set($ticket, 'used_at', Carbon::now());
                $ticket->update();
                $response = new ApiJsonResponse(200);
                return $response->toJson();
            } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
                $response = new ApiJsonResponse(400);
                return $response->toJson();
            }
        }
        $response = new ApiJsonResponse(200);
        return $response->toJson();
    }

    public function alerts($id, Request $request) {
        $event = CalendarEvent::findOrFail($id);
        $data = [
            'event' => $event
        ];
        return view('events.alerts')->with($data);
    }

    public function volunteers($id, Request $request) {
        $event = CalendarEvent::findOrFail($id);
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'event', $event);

        return view('events.volunteers')->with($data);
    }

    /**
     * Allows the user to edit event template
     * @param  integer  $id
     * @param  Request $request
     * @return View
     */
    public function settings($id, Request $request) {
        $split = CalendarEventTemplateSplit::whereHas('template')->findOrFail($id);
        $event = array_get($split, 'template');
        $this->authorize('update', $event);

        $calendars = Calendar::all();

        $chart = Purpose::whereNull('tenant_id')->orderBy('id', 'asc')->first();
        $form = Form::whereNull('tenant_id')->orderBy('id', 'asc')->first();

        $countries = $this->getCountries();
        $forms = Form::whereNotNull('tenant_id')->get();

        $dropDownForms = collect($forms)->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($form, 'id', 1) => array_get($form, 'name')]);

        $campaigns = collect(Campaign::orgOwned()->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::whereNotNull('tenant_id')->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [array_get($chart, 'id', 1) => 'None']);

        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'calendars', $calendars);
        array_set($data, 'event', $event);
        array_set($data, 'forms', $forms);
        array_set($data, 'dropDownForms', $dropDownForms);
        array_set($data, 'campaigns', $campaigns);
        array_set($data, 'charts', $charts);
        array_set($data, 'countries', $countries);
        array_set($data, 'calendar_id', array_get($event, 'calendar.id'));
        array_set($data, 'manager', ['id' => array_get($event, 'managers.0.id'), 'name' => array_get($event, 'managers.0.first_name') . ' ' . array_get($event, 'managers.0.last_name') . ' (' . array_get($event, 'managers.0.email_1') . ')']);
        array_set($data, 'split', $split);
        array_set($data, 'timezones', getAvalableTimezones());

        $start = null;
        $end = null;
        if( !array_get($split, 'template.is_all_day') ){
            $datetime = explode(' ', displayLocalDateTime(array_get($split, 'start_date'), array_get($split, 'template.timezone'))->toDayDateTimeString());
            $start = implode(' ', [$datetime[4], $datetime[5]]);
            $datetime = explode(' ', displayLocalDateTime(array_get($split, 'end_date'), array_get($split, 'template.timezone'))->toDayDateTimeString());
            $end = implode(' ', [$datetime[4], $datetime[5]]);
        }

        //array_set($data, 'start_date', array_get($request, 'date'));

        //array_set($data, 'start_date', array_get($request, 'date'));


        array_set($data, 'start_time', $start);
        array_set($data, 'end_time', $end);

        array_set($data, 'groupId', 'all');
        $allGroups = Group::orderBy('name')->get();
        $groups = ['all' => 'All People'];
        foreach ($allGroups as $gr) {
            $groups[array_get($gr, 'id')] = array_get($gr, 'name');
        }
        array_set($data, 'groups', $groups);
        
        return view('events.settings')->with($data);
    }

    public function excel($id, Request $request) {
        $split = CalendarEventTemplateSplit::findOrFail($id);
        $event = array_get($split, 'template');

        $repetitions = $event->splits;
        $contacts = $split->contacts;
        $filename = substr(str_slug(array_get($event, 'name')), 0, 28);

        $data = [
            'event' => $event,
            'repetitions' => $repetitions,
            'total' => $split->contactsCheckedIn->count(),
            'contacts' => $contacts,
            'title' => __('Name'),
            'filename' => $filename,
            'split' => $split
        ];

        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('events.excel', $data);
            });
        })->download('xlsx');
    }

    /**
     * Shows public Events individually
     * Note: this is public uri method handler
     * @param Uuid $id
     * @param Request $request
     * @return View
     */
    public function share($id, Request $request) {
        $data = $this->purchaseTickets($id, $request, true);
        $split_already_ended = $this->eventEnded($data['split']);
        if ($split_already_ended) $data['event_ended'] = true;
        return view('events.share')->with($data);
    }

    /**
     * NOTE: this is public uri method handler
     * @param  integer $id
     * @param  Request $request
     * @return Illuminate\View\View
     */
    public function publicDirectorySearch($id, Request $request){
        $tenant = TenantSubdomain::getTenant($request);

        if (!$tenant) {
            abort(404);
        }

        $redirect = EventSignin::checkReservation($request);
        if(!empty($redirect)){
            return $redirect;
        }

        $data = $this->purchaseTickets($id, $request, true);

        $split = CalendarEventTemplateSplit::find($id);
        /*
        $share = null;
        if(!is_null($split)){
            $share = route('events.share', ['id' => array_get($split, 'uuid')]);
            array_set($data, 'share', $share);
        }
        */
        //dd($data);

        return view('events.includes.share.share_v1', $data);
    }

    /**
     * NOTE: this is public uri method handler
     * @param  integer $id
     * @param  Request $request
     * @return Illuminate\View\View
     */
    public function all(Request $request) {
        $events = CalendarEvent::all();
        $tenant = TenantSubdomain::getTenant($request);
        $data = [
            'events' => $events,
            'tenant' => $tenant
        ];

        return view('events.public')->with($data);
    }

    /**
     * Process event registering, reservations, and if specified, auto check-in
     * TODO confirm that this either requires login or support unauthenticated use, clean up code (see Redirections::store)
     * @param  integer      $id
     * @param  SigninEvent $request
     * @return RedirectResponse
     */
    public function signin($id, SigninEvent $request) {
        \App\Classes\Redirections::store($request);
        $split = CalendarEventTemplateSplit::where('uuid', $id)->first();
        if (is_null($split))  abort(404);

        // Add Contact to event register
        $contact = Contact::findOrFail(array_get($request, 'id'));
        $register = EventRegister::find(array_get($request, 'registry_id'));
        if($register) {
            array_set($register, 'contact_id', array_get($request, 'id'));
            $register->save();
        }
        else $register = EventSignin::setRegistry($split, $contact);

        $contact->tags()->sync(array_get($split, 'tagInstance.id'), false);

        // pay for tickets
        $sum = $register->tickets()->sum(DB::raw('amount * price'));
        if($sum > 0){
            return redirect()->route('events.public.payment', ['id' => $id, 'register_id' => array_get($register, 'id')]);
        }

        // Still here? release hold, send email to manager and free tickets to Contact
        $register->tickets()->update([
            'temporary_hold' => false,
            'temporary_hold_ends_at' => null,
        ]);

        EventSignin::sendEmailToManager($split, $register, 'Event Signup', 'events.purchase.tickets.checkout');

        if(array_get($split, 'template.allow_auto_check_in') && !array_get($split, 'template.allow_reserve_tickets')){
            // commenting this out since this is creating a calendar_event_contact_register row for the second time
            //EventSignin::autoCheckInWithoutTickets($id, $request);

            $register->tickets()->update([
                'checked_in' => true,
                'used' => true,
                'used_at' => Carbon::now()
            ]);
        }

        //check if ticket credentials is needed;
        $template = $split->template;
        if ($template->ask_whose_ticket && count($register->tickets) > 1){
            $event_title = $template->name;
            $tickets = $register->tickets;
            return view('events.update_tickets_cred',compact('event_title','split','register','contact','tickets'));
        }
        
        EventSignin::sendTicketsToContact($split, $register, $contact);

        // check for form
        if ($split->template->linkedForm) {
            return $this->redirectToLinkedForm($split->template->linkedForm->uuid,
            $contact->id, $register->tickets);
        }

        $redirect = \App\Classes\Redirections::get();
        $data = [
            'split' => array_get($register, 'event'),
            'contact' => array_get($register, 'contact'),
            'redirect' => $redirect
        ];

        return view('events.signup-finished')->with($data);

        //we need to set new flow
        /*
        $data = [
            'id' => array_get($event, 'id'),
            'c' => array_get($contact, 'id'),
            'r' => array_get($register, 'id')
        ];

        if(array_get($event, 'template.allow_reserve_tickets') ){
            return redirect()->route('events.purchase.tickets', $data);
        }
        else{
            $ticket = $event->template->ticketOptions()->where('price', '<=', 0)->first();
            if(is_null($ticket)){
                //$ticket = new PurchasedTicket();
                $ticket = new TicketOption();
                array_set($ticket, 'tenant_id', array_get($event, 'tenant_id'));
                array_set($ticket, 'name', 'Free Ticket');
                array_set($ticket, 'price', 0);
                array_set($ticket, 'calendar_event_id', array_get($event, 'calendar_event_template_id'));
                $ticket->save();
            }

            array_set($request, 'ticket_amount', [1]);
            array_set($request, 'ticket_type', [array_get($ticket, 'id')]);
            CalendarEvents::buyTickets(array_get($event, 'id'), $request, false, $register);

            if (array_get($event, 'template.linkedForm.id', 0) > 1) {
                $data = [
                    'id' => array_get($event, 'template.linkedForm.uuid'),
                    'cid' => array_get($contact, 'id'),
                    'ticket_id' => array_get($register, 'tickets.0.id')
                ];
                return redirect()->route('forms.share', $data);
            }

            $custom_redirect = $this->redirect($event);
            if($custom_redirect !== false){
                return redirect($custom_redirect);
            }
        }
        */
    }

     /**
      * NOTE: this is public uri method handler
      * @param  integer $id
      * @param  Request $request
      * @param boolean  $return_data Optional. If specified and true, return data intead of view
      * @return Illuminate\View\View
      */
    public function purchaseTickets($id, Request $request, $return_data = false) {
        $tenant = TenantSubdomain::getTenant($request);
        if(is_numeric($id)){
            $split = CalendarEventTemplateSplit::whereHas('template')->findOrFail($id);
        }
        else{
            $split = CalendarEventTemplateSplit::where('uuid', $id)->whereHas('template')->firstOrFail();
        }

        $event = array_get($split, 'template');
        $contact = Contact::find(array_get($request, 'c'));
        $register = EventRegister::find(array_get($request, 'r'));
        if(is_null($register)){
            $register = EventRegister::find(array_get($request, 'registry'));
        }

        $tickets = $event->ticketOptions()
                    ->orderBy('price', 'desc')->get();

        $available_paid_tickets = $event->ticketOptions()->where([
            ['is_free_ticket', '=', false],
            ['allow_unlimited_tickets', '=', false]
        ])->sum('availability');

        $available_free_tickets = $event->ticketOptions()->where([
            ['is_free_ticket', '=', true],
            ['allow_unlimited_tickets', '=', false]
        ])->sum('availability');

        $available_unlimited_tickets = $event->ticketOptions()->where([
            ['allow_unlimited_tickets', '=', true]
        ])->count();

        $soldout = true;
        if($available_paid_tickets > 0 || $available_free_tickets > 0 || $available_unlimited_tickets > 0){
            $soldout = false;
        }

        $data = [
            'tenant' => $tenant,
            'event' => $event,
            'event_template' => $event,
            'split' => $split,
            'contact' => $contact,
            'register' => $register,
            'tickets' => $tickets,
            'ticket' => array_get($request, 't', 0),
            'soldout' => $soldout,
            'noTimer' => array_get($request, 'nt', 0)
        ];

        if($return_data){
            return $data;
        }

        return view('events.purchase-tickets')->with($data);
    }

    /**
     * TODO does this handle the "Form must be filled to checkin" logic
     * NOTE: this is public uri method handler
     * @param  integer  $id
     * @param  Request $request
     * @return RedirectResponse
     */
    public function purchaseTicketsCheckout($id, Request $request) 
    {
        if (array_has($request, 'ticket_type')) {
            foreach (array_get($request, 'ticket_type') as $ticket) {
                if (!$ticket) {
                    return redirect()->back()->with(['error' => 'Please select your tickets. This event has multiple tickets. Please select the ones you want to purchase.']);
                }
            }
        }
        
        Redirections::store($request, true);
        $split = CalendarEventTemplateSplit::find($id);
        if ($this->eventEnded($split)) abort(404);
        $contact = Contact::find(array_get($request, 'contact_id'));
        $register = EventRegister::find(array_get($request, 'register_id'));
        $tenant = array_get($split, 'template.tenant');

        //      Buy Tickets
        $autocheckin = (bool)array_get($split, 'template.allow_auto_check_in');
        $register = CalendarEvents::buyTickets($id, $request, $autocheckin, $register);


        // Ensure we have a contact
        if(is_null($contact)){//then we need to register contact, this will occur in events v2
            $event = array_get($split, 'template');
            if(array_get($event, 'version', 1) == 2){
                return redirect()->route('join.create', ['registry' => array_get($register, 'id')]);
            }
            else{
                return redirect()->route('events.public.search', ['id' => $id, 'registry' => array_get($register, 'id')]);
            }
        }

        // tickets should be paid before send to contact
        if( doubleval(array_get($request, 'total', 0)) > 0 ){
            $params = $request->only(['contact_id', 'total']);
            array_set($params, 'register_id', array_get($register, 'id'));

            return redirect()->route('events.public.payment', ['id' => $id, http_build_query($params)]);
        }
        // Still here? send email to manager and free tickets to Contact
        EventSignin::sendEmailToManager($split, $register, 'Event Signup', 'events.purchase.tickets.checkout');
        EventSignin::sendTicketsToContact($split, $register, $contact);


        // is there a linked form
        if ($split->template->linkedForm) {
            return $this->redirectToLinkedForm($split->template->linkedForm->uuid,
            $contact->id, $register->tickets);
        }

        if( array_get($split, 'template.allow_auto_check_in') ){
            $redirect = \App\Classes\Redirections::get();
            return redirect($redirect)->with('message', __('Complete. Thank You!'));
        }

        $custom_redirect = $this->redirect($split);
        if($custom_redirect !== false){
            return redirect($custom_redirect);
        }

        $data = [
            'id' => $id,
            'c' => array_get($contact, 'id'),
            'r' => array_get($register, 'id'),
            't' => array_get($request, 'ticket_id', 0)
        ];
        return redirect()->route('events.finish.tickets', $data);
    }


    protected function redirectToLinkedForm($id, $cid, $tickets)
    {
        // TODO support option forms for each ticket
        $ticket_id = $tickets->count() ? $tickets->first()->id : null;
        return redirect()->route('forms.share', compact('id','cid','ticket_id'));
    }

    public function finishTickets($id, $contact_id, $registry_id, Request $request) {
        $split = CalendarEventTemplateSplit::findOrFail($id);
        $event = array_get($split, 'template');
        $contact = Contact::findOrFail($contact_id);
        $register = EventRegister::findOrFail($registry_id);
        $data = [
            'contact' => $contact,
            'event' => $event,
            'registry' => $register,
            'ticket' => array_get($request, 't', 0),
            'split' => $split,
            'redirect' => \App\Classes\Redirections::get()
        ];

        return view('events.signup-finished')->with($data);
    }

    /**
     * Shows continue to give payment integration
     * NOTE: this is public uri method handler
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function payment($id, Request $request) {
        $tenant = TenantSubdomain::getTenant($request);
        $register = EventRegister::findOrFail(array_get($request, 'register_id'));
        $contact = Contact::findOrFail(array_get($request, 'contact_id', array_get($register, 'contact_id')));

        if(is_numeric($id)){
            $event = CalendarEventTemplateSplit::findOrFail($id);
        }
        else{
            $event = CalendarEventTemplateSplit::where('uuid', $id)->first();
        }

        $total = array_get($request, 'total');
        if(is_null($total)){
            $total = $register->tickets()->sum('price');
        }

        $params = [
            'event' => array_get($event, 'id'),
            'contact' => array_get($contact, 'id'),
            'register' => array_get($register, 'id'),
            'campaign' => str_random(),
            'chart' => str_random(),
        ];


        $altId = array_get(array_get($event, 'template.campaign')->altIds()->where('system_created_by', 'Continue to Give')->first(), 'alt_id');
        if (is_null($altId)) {
            $altId = array_get(array_get($event, 'template.chartOfAccount')->altIds()->where('system_created_by', 'Continue to Give')->first(), 'alt_id');
        }
        if (is_null($altId)) {
            $altId = array_get($tenant, 'altId.alt_id');
            array_set($params, 'campaign', array_get($event, 'template.campaign_id'));
            array_set($params, 'chart', array_get($event, 'template.purpose_id'));
        }
        $code = implode('-', $params);
        $url = route('events.finish', ['id' => array_get($event, 'id'), 'code' => $code]);

        $event_template = CalendarEvent::withoutGlobalScopes()->where('id', array_get($event, 'calendar_event_template_id'))->first();

        $data = [
            'tenant' => $tenant,
            'id' => $id,
            'event' => $event,
            'contact' => $contact,
            'type' => array_get($event_template, 'tax_deductible', 0) === 1 ? 'donation' : 'purchase',
            'code' => $code,
            'total' => $total,
            'alt_id' => $altId,
            'url' => $url,
            'form' => null,
            'event_template' => $event_template,
            'register' => $register,
        ];

        return view('events.payment')->with($data);
    }

    /**
     * Get redirected after payment
     * @param int $id
     * @param mixed $xcode
     * @param Request $request
     * @return View
     */
    public function finish($id, $xcode, Request $request) {
        $code = explode('-', $xcode);
        /*
          $code = [
          0 => event_id,
          1 => contact_id,
          2 => event_registry_id,
          3 => random-string(if campaign exists on c2g) || campaig-id(if campaign does NOT exists on c2g) ,
          4 => random-string(if char_of_account exists on c2g) || campaig-id(if campaign does NOT exists on c2g) ,
          ];
         */
        $donation = explode('-', array_get($request, 'donation'));
        /* $donation = [0 => alt-id, 1 => random-string]; */

        if (count($code) !== 5 || count($donation) !== 2) { //we are not getting response from c2g
            abort(500);
        }

        $alt_id = AltId::where([
                    ['alt_id', '=', $donation[0]],
                    ['relation_type', '=', TransactionTemplate::class]
                ])->first();

        $template = array_get($alt_id, 'getRelationTypeInstance');

        if (!is_null($template)) {
            $split = CalendarEventTemplateSplit::findOrFail($code[0]);
            $contact = Contact::findOrFail($code[1]);
            $register = EventRegister::findOrFail($code[2]);
            $tenant = array_get($split, 'template.tenant');

            array_set($register, 'paid', true);
            array_set($register, 'transaction_id', array_get($template, 'transactions.0.id'));
            $register->update();
            //if autocheckin is enabled
            if(array_get($split, 'template.allow_auto_check_in')){
                CalendarEvents::autoCheckInWithPurchasedTickets(array_get($split, 'id'), $register, true);
            }
            //if they get this part thent they have paid tickets, we remove the temporary hold
            $tickets = $register->tickets;
            DB::table('purchased_tickets')->whereIn('id', array_pluck($tickets, 'id'))->update([
                'temporary_hold' => false,
                'temporary_hold_ends_at' => null,
            ]);

            $templateSplit = array_get($template, 'splits.0');
            $transactionSplit = array_get($template, 'transactions.0.splits.0');

            $coa = Purpose::withoutGlobalScopes()->where('id', $code[4])->first();
            $campaign = Campaign::withoutGlobalScopes()->where('id', $code[3])->first();

            if (!is_null($coa)) {
                array_set($templateSplit, 'purpose_id', array_get($coa, 'id'));
                $templateSplit->update();

                array_set($transactionSplit, 'purpose_id', array_get($coa, 'id'));
                $transactionSplit->update();
            }

            if (!is_null($campaign)) {
                array_set($templateSplit, 'campaign_id', array_get($campaign, 'id'));
                $templateSplit->update();

                array_set($transactionSplit, 'campaign_id', array_get($campaign, 'id'));
                $transactionSplit->update();
            }


            EventSignin::sendEmailToManager($split, $register, 'Event Signup', 'events.purchase.tickets.checkout');
            
            //check if ticket credentials is needed;
            if ($split->template->ask_whose_ticket && count($tickets) > 1){
                $event_title = $split->template->name;
                return view('events.update_tickets_cred',compact('event_title','split','register','contact','tickets'));
            }
            
            EventSignin::sendTicketsToContact($split, $register, $contact, 'events.purchase.ticket.paid');

            // check for form
            if ($split->template->linkedForm) {
                return $this->redirectToLinkedForm($split->template->linkedForm->uuid,
                $contact->id, $register->tickets);
            }

            //if autocheckin is enabled
            if(array_get($split, 'template.allow_auto_check_in')){
                $redirect = session('redirect_url');
                \App\Classes\Redirections::destroy();
                return redirect($redirect)->with('message', __('Complete. Thank You!'));
            }

            $custom_redirect = $this->redirect($split);
            if($custom_redirect !== false){
                return redirect($custom_redirect);
            }

            return redirect()->route('events.finish.screen', ['id' => array_get($register, 'id')]);
        }

        abort(500);
    }

    public function ticket_credentials(Request $request){

        foreach (array_get($request,'ticket_id') as $key => $id){
            $ticket = PurchasedTicket::find($id);
            array_set($ticket,'first_name',array_get($request,'first_name')[$key]);
            array_set($ticket,'last_name',array_get($request,'last_name')[$key]);
            array_set($ticket,'email',array_get($request,'email')[$key]);
            $ticket->save();
        }

        $tickets = PurchasedTicket::query()->whereIn('id',array_get($request,'ticket_id'))->get();
        $split = $tickets->first()->registry->event;
        $register = $tickets->first()->registry;
        $contact = $tickets->first()->registry->contact;

        EventSignin::sendTicketsToContact($split, $register, $contact, 'events.purchase.ticket.paid');
        
        if ($split->template->linkedForm) {
            return $this->redirectToLinkedForm($split->template->linkedForm->uuid,
                $contact->id, $tickets);
        }

        if(array_get($split, 'template.allow_auto_check_in')){
            $redirect = session('redirect_url');
            \App\Classes\Redirections::destroy();
            return redirect($redirect)->with('message', __('Complete. Thank You!'));
        }

        $custom_redirect = $this->redirect($split);
        if($custom_redirect !== false){
            return redirect($custom_redirect);
        }

        return redirect()->route('events.finish.screen', ['id' => array_get($register, 'id')]);
    }

    public function finishScreen($id, Request $request){
        $registry = EventRegister::find($id);
        $redirect = \App\Classes\Redirections::get();
        $data = [
            'split' => array_get($registry, 'event'),
            'contact' => array_get($registry, 'contact'),
            'redirect' => $redirect
        ];

        return view('events.signup-finished')->with($data);
    }

    public function ajaxGet(Request $request) {
        $calendar = array_get($request, 'calendar');
        if( $calendar === '0' ){//showing all calendars
            $events = CalendarEvent::where('repeat', true)
                    ->get();
        }
        else{//just the current calendar
            $events = CalendarEvent::where([
                ['calendar_id', '=', $calendar],
                ['repeat', '=', true]
            ])
            ->get();
        }

        $start = array_get($request, 'start');
        $end = array_get($request, 'end');

        foreach ($events as $event) {
            CalendarEvents::splits($event, $start, null, $end);
        }

        $from = Carbon::parse($start)->startOfDay();
        $to = Carbon::parse($end)->endOfDay();
        if(array_get($request, 'public', 'false') === 'false'){
            if( auth()->check() ){
                $calendar = array_get($request, 'calendar', 0);
                $events = CalendarEvents::get($from, $to, $calendar);
            }
        }
        else{
            $calendar = array_get($request, 'calendar', 0);
            $calendars = explode('-', array_get($request, 'calendars'));
            $events = CalendarEvents::getPublic($from, $to, $calendars, $calendar);
        }

        return response()->json($events);
    }

    public function redirect($event) {
        if(!empty(array_get($event, 'template.custom_landing_page')) && filter_var(array_get($event, 'template.custom_landing_page'), FILTER_VALIDATE_URL)){
            return array_get($event, 'template.custom_landing_page');
        }

        if(!empty(array_get($event, 'template.custom_landing_page')) && !filter_var(array_get($event, 'template.custom_landing_page'), FILTER_VALIDATE_URL)){
            $url = 'http://'. array_get($event, 'template.custom_landing_page');
            return $url;
        }

        return false;
    }

    public function exportTickets($id, Request $request)
    {
        $eventRegister = EventRegister::with(['event.template', 'tickets', 'contact', 'transaction', 'releasedTickets']);

        if ($id === 'all') {
            $filename = substr(str_slug(auth()->user()->tenant->organization . ' Ticket Export'), 0, 28);
        } else {
            $eventRegister = $eventRegister->where('calendar_event_template_split_id', $id);

            $event = CalendarEventTemplateSplit::findOrFail($id);

            $filename = substr(str_slug($event->template->name), 0, 28);
        }

        if ($request->has('event_start')) {
            $eventRegister = $eventRegister->whereHas('event.template', function ($query) use ($request) {
                $query->where('start', '>=', $request->get('event_start'));
            });
        }

        if ($request->has('event_end')) {
            $eventRegister = $eventRegister->whereHas('event.template', function ($query) use ($request) {
                $query->where('end', '<=', $request->get('event_end') . ' 23:59:59');
            });
        }

        if ($request->has('ticket_start')) {
            $eventRegister = $eventRegister->whereHas('transaction', function ($query) use ($request) {
                $query->where('transaction_initiated_at', '>=', $request->get('ticket_start'));
            });
        }

        if ($request->has('ticket_end')) {
            $eventRegister = $eventRegister->whereHas('transaction', function ($query) use ($request) {
                $query->where('transaction_initiated_at', '<=', $request->get('ticket_end') . ' 23:59:59');
            });
        }

        $registries = $eventRegister->get();

        if ($id === 'all') {
            $allFormsNamesAndLabels = Form::getFormsNamesAndLabels();
        } else {
            $allFormsNamesAndLabels = Form::getFormsNamesAndLabels([$event->template->form]);
        }

        $registries->map(function ($registry) use ($allFormsNamesAndLabels) {
            $registry->tickets->map(function ($ticket) use ($allFormsNamesAndLabels) {
                $extras = [];

                if ($ticket->formEntry) {
                    foreach ($ticket->formEntry->jsonValues as $name => $val) {
                        if (!$allFormsNamesAndLabels->has($name) && !empty($val)) {
                            $extras[$name] = $val;
                        }
                    }
                }

                $ticket->extras = empty($extras) ? '' : json_encode($extras);
            });
        });

        $allFormsNamesAndLabelsFlipped = [];

        $allFormsNamesAndLabels->each(function ($item, $key) use (&$allFormsNamesAndLabelsFlipped) {
            if (!isset($allFormsNamesAndLabelsFlipped[$item])) {
                $allFormsNamesAndLabelsFlipped[$item] = [$key];
            } else {
                $allFormsNamesAndLabelsFlipped[$item][] = $key;
            }
        });

        $data = [
            'registries' => $registries->sortBy(function ($registry) {
                return [array_get($registry, 'event.template.name'), array_get($registry, 'contact.first_name'), array_get($registry, 'contact.last_name'), array_get($registry, 'id')];
            }),
            'whose_ticket' => $registries->where('event.template.ask_whose_ticket','=',1)->count(),
            'filename' => $filename,
            'allFormsNamesAndLabels' => $allFormsNamesAndLabels->unique()->sort(),
            'allFormsNamesAndLabelsFlipped' => $allFormsNamesAndLabelsFlipped
        ];

        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('events.export-tickets', $data);
            });
        })->download('xlsx');
    }

    public function exportToIcs(){
        $from = Carbon::parse(array_get(request(),'from_date'))->startOfDay();
        $to = Carbon::parse(array_get(request(),'to_date'))->endOfDay();
        $calendars = array_get(request(),'calendar');
        return CalendarEvents::exportToIcs($from,$to,$calendars);
    }

    /**
     * @param $split
     * @return bool
     */
    private function eventEnded($split)
    {
        return Carbon::now()->gt(Carbon::parse($split->end_date));
    }
    
    public function deleteTicket($id)
    {
        $ticket = PurchasedTicket::withTrashed()->findOrFail($id);
        
        if (array_get($ticket, 'deleted_at')) {
            $ticket->forceDelete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);;
        }
    }
    
    public function checkinReport($id, Request $request)
    {
        $event = CalendarEventTemplateSplit::findOrFail($id);
        $reportType = array_get($request, 'reportType');
        $groups = Group::with('contacts')->whereIn('id',  array_get($request, 'groupIds', []))->orderBy('name')->get();
        $signups = EventRegister::with(['contact', 'ticket'])->where('calendar_event_template_split_id', array_get($event, 'id'))->get();
        $signupIds = $signups->pluck('contact_id')->toArray();
        
        if ($reportType === 'all') {
            $pdf = PDF::loadView('events.includes.checkin.report.all', compact('event', 'signups'))->setPaper('letter', 'landscape');
        } elseif ($reportType === 'group') {
            $groupSignups = [];
            $signupsNotInGroups = [];
            $allGroupContactIds = [];
            
            foreach ($groups as $group) {
                $groupSignups[array_get($group, 'name')] = [];
                $groupContacts = array_get($group, 'contacts');
                $groupContactIds = $groupContacts->pluck('id')->toArray();
                $allGroupContactIds = array_merge($allGroupContactIds, $groupContactIds);
                
                foreach ($signups as $signup) {
                    if (in_array(array_get($signup, 'contact.id'), $groupContactIds)) {
                        $groupSignups[array_get($group, 'name')][] = $signup;
                    }
                }
                
                foreach ($groupContacts as $contact) {
                    if (!in_array(array_get($contact, 'id'), $signupIds)) {
                        $groupSignups[array_get($group, 'name')][] = ['contact' => $contact];
                    }
                }
            }
            
            foreach ($signups as $signup) {
                if (!in_array(array_get($signup, 'contact.id'), $allGroupContactIds)) {
                    $signupsNotInGroups[] = $signup;
                }
            }
            
            $pdf = PDF::loadView('events.includes.checkin.report.group', compact('event', 'signupsNotInGroups', 'groupSignups'))->setPaper('letter', 'landscape');
        } else {
            return false;
        }
        
        $filename = auth()->user()->tenant->organization.' - Checkin Report - '.date('F j, 2024 His');
        
        return $pdf->download($filename);
    }
}
