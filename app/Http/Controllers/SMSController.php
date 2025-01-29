<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use DB;
use App\Classes\Twilio\TwilioAPI;
use App\Models\Contact;
use App\Models\SMSPhoneNumber;
use App\Models\SMSContent;
use App\Models\Lists;
use App\Traits\MassMessageTrait;
use App\Classes\Shared\Emails\Charts\Pie\PieChart;
use App\Models\SMSTracking;
use App\Models\SMSSent;
use App\Models\Tenant;
use App\Classes\Twilio\TwilioSender;
use App\MPLog;
use App\Http\Requests\StoreTwilioRegisterForm;

class SMSController extends Controller
{
    use MassMessageTrait;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $smsPhoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
        $noPadding = true;

        return view('communications.sms.index')->with(compact('smsPhoneNumbers', 'noPadding'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $hasSMSPhoneNumber = 'false';
        $smsPhoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
        $defaultSMSPhoneNumberId = false;
        
        if ($smsPhoneNumbers && $smsPhoneNumbers->count()) {
            $hasSMSPhoneNumber = 'true';
            $defaultSMSPhoneNumberId = $smsPhoneNumbers->first()->id;
        }

        $data = compact('hasSMSPhoneNumber', 'defaultSMSPhoneNumberId');
        $data = array_merge($data, $request->input());
        return view('communications.sms.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sms = SMSContent::findOrFail($id);
        $list = $sms->getRelationTypeInstance;
        $chart = PieChart::graph($sms);
        $sentOut = $sms->sent()->with('to')->orderBy('id', 'desc')->paginate();

        $data = [
            'list' => $list,
            'sms' => $sms,
            'total' => $sms->sent()->get()->count(),
            'chart' => $chart,
            'sentOut' => $sentOut
        ];

        return view('communications.sms.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $sms = SMSContent::with('list')->findOrFail($id);
        
        $hasSMSPhoneNumber = 'false';
        $smsPhoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
        $defaultSMSPhoneNumberId = false;
        
        if ($smsPhoneNumbers && $smsPhoneNumbers->count()) {
            $hasSMSPhoneNumber = 'true';
            $defaultSMSPhoneNumberId = $smsPhoneNumbers->first()->id;
        }

        $data = compact('sms', 'hasSMSPhoneNumber', 'defaultSMSPhoneNumberId');
        $data = array_merge($data, $request->input());
        return view('communications.sms.edit')->with($data);
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
        $sms = SMSContent::findOrFail($id);
        SMSContent::destroy($id);

        return response()->json(['success' => true]);
    }

    public function showAvailablePhoneNumbers(Request $request){
        if(!empty(array_get($request, 'code'))){
            $twilio = new TwilioAPI();
            $numbers = $twilio->getAvailablePhoneNumbers(array_get($request, 'code'));
            return response()->json($numbers);
        }
        return response()->json([]);
    }

    public function buyPhoneNumber(Request $request){
        if(!empty(array_get($request, 'phone'))){
            $twilio = new TwilioAPI();
            $result = $twilio->buyPhoneNumber(array_get($request, 'phone'));
            if(array_get($result, 'status') == 200){
                $result['response'] = $twilio->arrayTransform($result['response']);
                $phone = mapModel(new SMSPhoneNumber(), $result['response']);
                array_set($phone, 'data', json_encode($result['response']));
                array_set($phone, 'tenant_id', array_get(auth()->user(), 'tenant_id'));
                array_set($phone, 'phone_number', array_get($result, 'response.phoneNumber'));
                array_set($phone, 'name', array_get($request, 'name'));
                array_set($phone, 'notify_to_email', array_get($request, 'email_notifications'));
                array_set($phone, 'notify_to_phone', array_get($request, 'phone_notifications'));
                array_set($phone, 'notify_to_phone_numbers', onlyNumbers(array_get($request, 'phone_notifications')));
                if (!empty(array_get($request, 'contacts'))) {
                    array_set($phone, 'notify_to_contacts', implode(',', array_get($request, 'contacts')));
                }
                $phone->save();
                auth()->user()->tenant->updatePhoneNumberFee();
            }

            return response()->json($result,$result['status']);
        }
        return response()->json("empty",400);
    }

    public function test(Request $request)
    {
        if (array_get($request, 'test')) {
            $from = SMSPhoneNumber::findOrFail(array_get($request, 'sms_phone_number_id'));
            $twilio = new TwilioAPI();
            $result = $twilio->sendTestMessage($from, array_get($request, 'test_phone_number'), array_get($request, 'content'));
            return gettype($result) == 'array' ? response()->json($result,$result['status']) : response()->json($result);
        }

        return response()->json(['status' => 400, 'To send a message you need to set "test" property as true']);
    }

    public function previewSummary(Request $request){
        $summary = $this->getSummary($request);
        return response()->json($summary);
    }

    public function sendSms(Request $request)
    {
        //$json = '{"data":{"id":13,"page":2,"pages":1,"type":"sms","audience":{"send_all":false,"send_number_of_messages":1,"do_not_send_within_number_of_days":5},"message":{"test":false,"test_phone_number":"","sms_phone_number_id":26,"list_id":0,"datatable_state_id":0,"content":"a"},"tags":{"include":[],"exclude":[]},"actions":[{"input":"message_sent","text":"Message sent","tag":0},{"input":"message_delivered","text":"Message delivered","tag":0},{"input":"message_undelivered","text":"Message undelivered","tag":0},{"input":"message_failed","text":"Message failed","tag":0}]}}';
        $settings = array_get($request, 'data');

        if(!empty($settings)){
            $sms = $this->storeSettings($settings);
            $queue = $this->queue($request, $sms);
            return response()->json($queue);
        }
        return response()->json([]);
    }

    public function tracking(Request $request){
        $sms = SMSContent::findOrFail(array_get($request, 'sms'));
        $list = $sms->lists()->first();
        $sent = SMSSent::findOrFail(array_get($request, 'track'));
        $history = SMSTracking::where('sms_sent_id', array_get($sent, 'id'))->orderBy('id', 'desc')->get();
        $data = [
            'sms' => $sms,
            'list' => $list,
            'sent' => $sent,
            'history' => $history
        ];

        return view('communications.sms.history')->with($data);
    }

    public function trackWebHook(Request $request){
        $twilio = new TwilioAPI();
        $twilio->statusCallback($request);
    }

    public function reply($id, Request $request){
        $sent = SMSSent::findOrFail(base64_decode($id));
        $data = [
            'sent' => $sent,
            'id' => $id
        ];
        return view('communications.sms.reply')->with($data);
    }

    public function sendReply($id, Request $request)
    {
        $sent = SMSSent::findOrFail(base64_decode($id));
        $to = array_get($sent, 'from');
        $phone = SMSPhoneNumber::where('phone_number', array_get($sent, 'content.sms_phone_number_to'))->first();
        
        if (empty($phone)) {
            abort(404);
        }
        
        $content = new SMSContent();
        array_set($content, 'tenant_id', array_get($sent, 'tenant_id'));
        array_set($content, 'sms_phone_number_from', array_get($phone, 'phone_number'));
        array_set($content, 'content', array_get($request, 'content'));
        array_set($content, 'queued_by', implode('.', [SMSController::class, __FUNCTION__]));
        array_set($content, 'relation_id', array_get($to, 'id'));
        array_set($content, 'relation_type', get_class($to));
        array_set($content, 'track_and_tag_events', '[]');

        if($content->save()){
            $sms = new SMSSent();
            array_set($sms, 'tenant_id', array_get($sent, 'tenant_id'));
            array_set($sms, 'to_contact_id', array_get($to, 'id'));
            array_set($sms, 'from_contact_id', auth()->user()->contact->id);
            array_set($sms, 'sms_content_id', array_get($content, 'id'));
            array_set($sms, 'in_reply_to', array_get($sent, 'id'));

            if($sms->save()){
                return redirect()->back()->with(['message' => 'Message sent succesfully']);
            }
        }
        abort(404);
    }

    public function replyIncomeWebHook(Request $request){
        //$json = '{"ToCountry":"US","ToState":"GA","SmsMessageSid":"SMc8361bd39bdf8f0caa7cdbc8d3124240","NumMedia":"0","ToCity":"ATLANTA","FromZip":null,"SmsSid":"SMc8361bd39bdf8f0caa7cdbc8d3124240","FromState":"Oax.","SmsStatus":"received","FromCity":"Zimatlan De Alvarez","Body":"Hola","FromCountry":"MX","To":"+14702608304","ToZip":null,"NumSegments":"1","MessageSid":"SMc8361bd39bdf8f0caa7cdbc8d3124240","AccountSid":"AC46fa212461064e808c176e74e0fc56a9","From":"+529511232273","ApiVersion":"2010-04-01"}';
        //$request = json_decode($json, true);

        $twilio = new TwilioAPI();
        $twilio->replyReceivedCallback($request);
    }

    public function storeInDatabase(Request $request)
    {
        $phone = SMSPhoneNumber::findOrFail(array_get($request, 'data.message.sms_phone_number_id'));
        $id = array_get($request, 'data.id', 0);
        $type = array_get($request, 'data.type');
        $list_id = array_get($request, 'data.message.list_id', 0);
        if($list_id == 0){
            $list_id = null;
        }

        if($id > 0){
            $message = $type == 'sms' ? SMSContent::findOrFail($id) : null;
        }
        else{
            $message = $type == 'sms' ? new SMSContent() : null;
        }

        array_set($message, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
        array_set($message, 'list_id', $list_id);
        array_set($message, 'sms_phone_number_from', array_get($phone, 'phone_number'));
        array_set($message, 'content', array_get($request, 'data.message.content'));
        array_set($message, 'relation_id', is_null($list_id) ? 0 : $list_id);
        array_set($message, 'relation_type', Lists::class);
        array_set($message, 'send_to_all', array_get($request, 'data.audience.send_all'));
        array_set($message, 'send_number_of_messages', array_get($request, 'data.audience.send_number_of_messages'));
        array_set($message, 'do_not_send_within_number_of_days', array_get($request, 'data.audience.do_not_send_within_number_of_days'));
        array_set($message, 'track_and_tag_events', json_encode(array_get($request, 'data.actions', null)));
        array_set($message, 'queued_by', implode('.', [self::class, __FUNCTION__]));
        $message->save();
        $message->includeTags()->sync(array_get($request, 'data.tags.include'));
        $message->excludeTags()->sync(array_get($request, 'data.tags.exclude'));

        return response()->json([
            'id' => array_get($message, 'id', 0)
        ]);
    }

    public function getTextConversationPreview(Request $request)
    {
        $status = array_get($request, 'status');
        $search = array_get($request, 'search');
        $read = array_get($request, 'read');
        $smsPhoneNumbersIds = array_get($request, 'smsPhoneNumbers');
        $toOrFrom = $status === 'received' ? 'from' : 'to';
        $toOrFrom2 = $status === 'received' ? 'to' : 'from';
        $smsPhoneNumbers = auth()->user()->tenant->SMSPhoneNumbers->whereIn('id', $smsPhoneNumbersIds);
        
        $statusArray = [$status];
        if ($status === 'sent') {
            $statusArray[] = 'Queued';
        }
        
        $sms = SMSSent::whereIn('status', $statusArray)->whereHas('content', function ($query) use ($smsPhoneNumbers, $toOrFrom2) {
            $query->where(function ($q) use ($smsPhoneNumbers, $toOrFrom2) {
                $q->whereIn('sms_phone_number_'.$toOrFrom2, $smsPhoneNumbers->pluck('phone_number'))->orWhereNull('sms_phone_number_'.$toOrFrom2);
            })->isNotScheduled();
        })->whereHas($toOrFrom)->where($toOrFrom2.'_contact_id', auth()->user()->contact->id);
        
        if ($search) {
            $searchEx = explode(' ', $search);
            $sms = $sms->whereHas($toOrFrom, function ($query) use ($searchEx) {
                foreach ($searchEx as $searchPiece) {
                    $query->where(function ($query) use ($searchPiece) {
                        $query->where('first_name', 'like', '%'.$searchPiece.'%')
                            ->orWhere('last_name', 'like', '%'.$searchPiece.'%')
                            ->orWhere('preferred_name', 'like', '%'.$searchPiece.'%');
                    });
                }
            });
        }
        
        if ($read === 'unread') {
            $sms = $sms->where('read', 0);
        }
        
        $page = array_get($request, 'page', 1);
        $sms = $sms->orderBy('id', 'desc')->get()->unique($toOrFrom.'_contact_id')->values();
        $lastPage = floor($sms->count() / 15) + 1;
        $sms = $sms->forPage($page, 15)->all();
        
        $html = view('communications.sms.includes.text-conversation-preview')->with(compact('sms'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'lastPage' => $lastPage
        ]);
    }
    
    public function getTexts(Request $request)
    {
        $contact = Contact::findOrFail(array_get($request, 'contact'));
        
        $smsPhoneNumbers = auth()->user()->tenant->SMSPhoneNumbers->whereIn('id', array_get($request, 'smsPhoneNumbers'))->pluck('phone_number');
        
        $smsReverse = SMSSent::with('content')->whereIn('id', $contact->texts->pluck('id'))
                ->where(function ($query) use ($smsPhoneNumbers) {
                    $query->where(function ($query) use ($smsPhoneNumbers) {
                        $query->where('status', 'received')
                                ->whereHas('content', function ($query) use ($smsPhoneNumbers) {
                                    $query->whereIn('sms_phone_number_to', $smsPhoneNumbers)->isNotScheduled();
                                });
                    })->orWhere(function ($query) use ($smsPhoneNumbers) {
                        $query->where('status', '<>', 'received')
                                ->whereHas('content', function ($query) use ($smsPhoneNumbers) {
                                    $query->whereIn('sms_phone_number_from', $smsPhoneNumbers)->isNotScheduled();
                                });
                    });
                })->orderBy('id', 'desc')->paginate(10);
        
        $sms = $smsReverse->reverse()->all();
                
        $unread = $contact->unreadTexts;
        foreach ($unread as $item) {
            $item->read = 1;
            $item->save();
        }
        
        $phoneNumbers = auth()->user()->contact->SMSPhoneNumbers;
        $phoneNumbersSelect = [];
        $hasPhoneNumber = false;
        if ($phoneNumbers->count()) {
            $hasPhoneNumber = true;
            foreach ($phoneNumbers as $phone) {
                $phoneNumbersSelect[array_get($phone, 'id')] = array_get($phone, 'name_and_number');
            }
        }
        
        if (array_get($request, 'loadMoreTexts')) {
            $html = view('people.contacts.includes.texts')->with(compact('sms'))->render();
        } else {
            $html = view('people.contacts.includes.texts-with-form')->with(compact('sms', 'contact', 'phoneNumbersSelect', 'hasPhoneNumber'))->render();
        }
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'lastPage' => $smsReverse->lastPage()
        ]);
    }
    
    public function markSmsAsReadOrUnread(Request $request)
    {
        $read = array_get($request, 'read');
        $smsIds = array_get($request, 'sms');
        
        $sms = SMSSent::whereIn('id', $smsIds)->get();
        
        foreach ($sms as $item) {
            $item->read = $read;
            $item->save();
        }
        
        return response()->json(['success' => true]);
    }
    
    public function massText(Request $request)
    {
        $sms = SMSContent::select([
            'sms_content.id',
             'lists.name as list_name',
             'sms_content.content as content',
             'sms_content.created_at',
             DB::raw('"SMS" as type'),
             DB::raw('COUNT(DISTINCT sms_sent.to_contact_id) AS sent_count'),
         ])
        ->leftJoin('lists', 'lists.id', '=', 'sms_content.list_id')
        ->leftJoin('sms_sent', 'sms_content.id', '=', 'sms_sent.sms_content_id')
        ->where('relation_type', Lists::class)
        ->groupBy('sms_content.id')
        ->orderBy('created_at', 'desc')->get();
        
        $page = array_get($request, 'page', 1);
        $perPage = 15;
        $collection = collect($sms);
        $paginate = new LengthAwarePaginator($collection->forPage($page, $perPage), $collection->count(), $perPage, $page, ['path'=>route('communications.index')]);

        $data = [
            'messages' => $paginate
        ];

        return view('communications.sms.mass-text')->with($data);
    }
    
    public function showTwilioRegisterForm()
    {
        $host = request()->getHttpHost();
        $ex = explode('.', $host);
        $tenant = Tenant::where('subdomain', $ex[0])->firstOrFail();
        
        return view('communications.sms.newsletter')->with(compact('tenant'));
    }
    
    public function storeTwilioRegisterForm(StoreTwilioRegisterForm $request)
    {
        return redirect()->route('newsletter.success');
    }
    
    public function showTwilioRegisterFormSuccess()
    {
        $host = request()->getHttpHost();
        $ex = explode('.', $host);
        $tenant = Tenant::where('subdomain', $ex[0])->firstOrFail();
        
        return view('communications.sms.newsletter-success')->with(compact('tenant'));
    }
    
    public function showTwilioPrivacy()
    {
        $host = request()->getHttpHost();
        $ex = explode('.', $host);
        $tenant = Tenant::where('subdomain', $ex[0])->firstOrFail();
        $address = $tenant->users()->OrganizationOwner()->first()->contact->full_address;
        
        return view('communications.sms.privacy')->with(compact('tenant', 'address'));
    }
    
    public function getTextsScheduled(Request $request)
    {
        $search = array_get($request, 'search');
        $smsPhoneNumbersIds = array_get($request, 'smsPhoneNumbers');
        $smsPhoneNumbers = auth()->user()->tenant->SMSPhoneNumbers->whereIn('id', $smsPhoneNumbersIds);
        
        $sms = SMSContent::with(['list', 'sent'])->isScheduled()
                ->where('created_by', auth()->user()->id)
                ->whereIn('sms_phone_number_from', $smsPhoneNumbers->pluck('phone_number'));
        
        if ($search) {
            $searchEx = explode(' ', $search);
            
            if (strtolower($search) === 'everyone') {
                $sms = $sms->whereDoesntHave('list');
            } else {
                $sms = $sms->whereHas('list', function ($query) use ($searchEx) {
                    foreach ($searchEx as $searchPiece) {
                        $query->where('name', 'like', '%'.$searchPiece.'%');
                    }
                });
            }
        }
        
        $page = array_get($request, 'page', 1);
        $sms = $sms->orderBy('id', 'asc')->get();
        $lastPage = floor($sms->count() / 15) + 1;
        $sms = $sms->forPage($page, 15)->all();
        
        $html = view('communications.sms.includes.texts-scheduled')->with(compact('sms'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'lastPage' => $lastPage
        ]);
    }
    
    public function viewSchedule(Request $request)
    {
        $sms = SMSContent::findOrFail(array_get($request, 'sms'));
        
        $html = view('communications.sms.includes.view-schedule')->with(compact('sms'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
