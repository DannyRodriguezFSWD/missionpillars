<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Lists;
use App\Models\Folder;
use App\Constants;
use App\Traits\TagsTrait;

use App\Models\Email;
use App\Models\EmailSent;
use App\Models\Contact;

use App\Models\EmailTracking;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\Unsubscribe;
use App\Classes\Email\EmailQueue;
use App\Http\Requests\Emails\CountNumberEmails;
use App\Classes\Shared\Emails\Charts\Pie\PieChart as EmailPieChart;

use App\Traits\Emails\EmailTrait;

class EmailsController extends Controller
{
    use TagsTrait, EmailTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $emails = Email::where('relation_type', Lists::class);
        
        $data = [
            'total' => $emails->count(),
            'emails' => $emails->orderBy('id', 'desc')->paginate()
        ];
        
        return view('emails.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $lists = collect(Lists::orderBy('name')->get())->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, [0 => 'Everyone']);
        
        $data = ['lists' => $lists, 'email' => null];
        
        return view('emails.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = array_get($request, 'list_id');
        $list = $id > 0 ? Lists::findOrFail($id) : null;
        
        $email = mapModel(new Email(), $request->all());
        array_set($email, 'list_id', array_get($list, 'id'));
        array_set($email, 'relation_id', array_get($list, 'id', 0));
        array_set($email, 'relation_type', Lists::class);
        array_set($email, 'uuid', \Ramsey\Uuid\Uuid::uuid1());
        array_set($email, 'queued_by', 'list.email.store');
        
        if (auth()->user()->tenant->emails()->save($email)) {
            return redirect()->route('emails.count', ['email' => array_get($email, 'id'), 'list' => array_get($list, 'id')]);
        }
        return redirect()->route('lists.index')->with('error', __('An error occurred trying to send email'));
    }
    
    public function count($id, Request $request) {
        $email = Email::findOrFail($id);
        $list = null;
        if( !is_null(array_get($email, 'list_id')) && get_class($email->getRelationTypeInstance) === Lists::class ){
            $list = $email->getRelationTypeInstance;
        }
        
        $data = [
            'email' => $email,
            'list' => $list
        ];
        return view('emails.count')->with($data);
    }

    public function storeNumberOfEmails($id, CountNumberEmails $request) {
        $email = Email::findOrFail($id);
        array_set($email, 'do_not_send_to_previous_receivers', 0);
        mapModel($email, $request->all());
        
        if( array_has($request, 'send_to_all') ){
            array_set($email, 'send_number_of_emails', 0);
        }
        else{
            array_set($email, 'send_to_all', false);
        }
        
        if( $email->update() ){
            //return redirect()->route('emails.exclude', ['id' => array_get($email, 'id'), 'list' => array_get($request, 'list')]);
            return redirect()->route('emails.track', ['id' => array_get($email, 'id')]);
        }
        
    }
    
    public function track($id, Request $request) {
        $email = Email::findOrFail($id);
        $list = is_null(array_get($email, 'list_id')) ? null : $email->getRelationTypeInstance;
        
        $track = [];
        if(!is_null(array_get($email, 'track_and_tag_events'))){
            $track = json_decode(array_get($email, 'track_and_tag_events'), true);
        }
        
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'email', $email);
        array_set($data, 'list', $list);
        $folderDropdown = collect(Folder::where('type', 'TAGS')->orderBy('name')->get())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);
        array_set($data, 'track', $track);
        
        return view('emails.track')->with($data);
    }
    
    public function storeTrack($id, Request $request) {
        $email = Email::findOrFail($id);
        if(array_has($request, 'status')){
            $status = [];
            foreach (array_get($request, 'status') as $item){
                array_set($status, $item, array_get($request, $item));
            }
            $exclude = json_encode($status);
            array_set($email, 'track_and_tag_events', $exclude);
            $email->update();
        }
        return redirect()->route('emails.exclude', ['id' => array_get($email, 'id')]);
    }
    
    public function selectTagsToExclude($id, Request $request) {
        $email = Email::findOrFail($id);
        $included = array_pluck(array_get($email, 'includeTags'), 'id');
        $excluded = array_pluck(array_get($email, 'excludeTags'), 'id');
        
        $list = is_null(array_get($email, 'list_id')) ? null : $email->getRelationTypeInstance;
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'email', $email);
        array_set($data, 'list', $list);
        $folderDropdown = collect(Folder::where('type', 'TAGS')->orderBy('name')->get())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);
        array_set($data, 'folderDropdown', $folderDropdown);
        array_set($data, 'included', $included);
        array_set($data, 'excluded', $excluded);
        
        return view('emails.exclude')->with($data);
    }
    
    public function excludeTags($id, Request $request) {
        $email = Email::findOrFail($id);
        
        $include = array_get($request, 'tags', []);
        $email->includeTags()->sync($include);
        
        $exclude = array_get($request, 'not', []);
        $email->excludeTags()->sync($exclude);
        
        return redirect()->route('emails.getconfirm', ['id' => array_get($email, 'id')]);
    }
    
    public function tags(Request $request) {
        $vars = $request->all();
        $folder = Folder::findOrFail(array_get($vars, 'folder'));
        $tag = Tag::findOrCreate(array_get($vars, 'tag'), $folder);
        
        if(!is_null($tag)){
            return response()->json($tag);
        }
        
        return response()->json(false);
    }
    
    public function getConfirm($id, Request $request) {
        $email = Email::findOrFail($id);
        $list = is_null(array_get($email, 'list_id')) ? null : $email->getRelationTypeInstance;

        //$summary = $list->summary($email);
        $summary = $email->summary();

        $track = json_decode(array_get($email, 'track_and_tag_events', '[]'), true);
        $actions = [];
        foreach ($track as $property => $value){
            $event = [
                'event' => title_case($property),
                'tag' => title_case(array_get(Tag::find($value), 'name'))
            ];
            array_push($actions, $event);
        }
        
        $data = [
            'email' => $email,
            'list' => $list,
            'actions' => $actions,
            'contacts' => array_get($summary, 'contacts', []),
            'contacts_not_included' => array_get($summary, 'contacts_not_included', []),
            'include_lists_tags' => array_get($summary, '$include_list_tags', []),
            'exclude_lists_tags' => array_get($summary, 'exclude_list_tags', []),
            'include_email_tags' => array_get($summary, 'include_email_tags', []),
            'exclude_email_tags' => array_get($summary, 'exclude_email_tags', []),
        ];
        
        return view('emails.summary', $data);
    }
    
    public function postConfirm($id, Request $request) {
        $email = Email::findOrFail($id);
        $email->sendEmail();
        
        return redirect()->route('emails.finish', ['id' => array_get($email, 'id')])->with('message', __('Email successfully added to queue'));
    }
    
    public function finish($id) {
        return view('emails.finish', ['id' => $id]);
    }
    
    public function email($id, Request $request) {
        if (array_get($request, 'action') === 'preview') {
            return $this->sendPreview($id, $request);
        } else {
            return $this->setEmailQueue($id, $request);
        }

        return redirect()->route('contacts.compose', ['id' => $id])->with('error', __('An error occurred trying to send email'));
    }
    
    public function sendPreview($id, $request) {
        $content = $request->input('content');
        
        $args = [
            'from_name' => array_get($request, 'from_name'),
            'from_email' => array_get($request, 'from_email'),
            'subject' => array_get($request, 'subject'),
            'reply_to' => array_get($request, 'reply_to', null),
            'content' => $content,
            'cc_secondary' => array_get($request,'cc_secondary') == 'true' ? 1 : null,
            'model' => auth()->user(),
            'include_transactions' => array_get($request,'include_transactions') == 'false' ? 0 : 1,
            'transaction_start_date' => array_get($request,'include_transactions') == 'false' ? null : Carbon::parse(array_get($request,'transaction_start_date')),
            'transaction_end_date' => array_get($request,'include_transactions') == 'false' ? null : Carbon::parse(array_get($request,'transaction_end_date')),
            'queued_by' => 'email.preview',
            'email_editor_type' => array_get($request, 'email_editor_type', 'tiny')
        ];
        
        if ($request->has('send_to')) {
            $contacts = Contact::query()->whereIn('id', $request->send_to)->get();

            if (count($contacts)) {
                $attachments = Document::where('relation_id', $id)->where('relation_type', \App\Models\Communication::class)->get();
                foreach ($contacts as $contact) {
                    EmailQueue::set($contact, $args, $attachments);
                }
            }
        } else {
            $contact = array_get(auth()->user(), 'contact');
            
            EmailQueue::set($contact, $args);
        }
        
        return $request->ajax() ? response()->json(true) : redirect()->route('lists.composer', ['id' => $id])->with('message', __('Email preview successfully sent'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = Email::findOrFail($id);
        $list = $email->getRelationTypeInstance;
        
        $chart = EmailPieChart::graph($email);
        $sentOut = $email->sent()->orderBy('id', 'desc')->paginate();
        $data = [
            'list' => $list,
            'email' => $email,
            'total' => $email->sent()->get()->count(),
            'chart' => $chart,
            'sentOut' => $sentOut
        ];
        
        return view('emails.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $email = Email::findOrFail($id);
        $exclude = [];
        if(!is_null(array_get($email, 'exclude_tags'))){
            $exclude = explode(',', array_get($email, 'exclude_tags'));
        }
        
        $list = null;
        if( !is_null(array_get($email, 'list_id')) ){
            $list = $email->getRelationTypeInstance;
        }
        
        $lists = collect(Lists::orderBy('name')->get())->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'name');
            return $carry;
        }, [0 => 'Everyone']);
        
        $data = ['email' => $email, 'list' => $list, 'lists' => $lists];
        
        return view('emails.edit')->with($data);
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
        $email = Email::findOrFail($id);
        $list = Lists::find(array_get($request, 'list_id'));
        
        mapModel($email, $request);
        if(is_null($list)){
            array_set($email, 'list_id', null);
        }
        
        array_set($email, 'relation_id', array_get($list, 'id', 0));
        array_set($email, 'relation_type', Lists::class);
        
        if ($email->update()) {
            return redirect()->route('emails.count', ['email' => array_get($email, 'id')]);
        }
        return redirect()->route('emails.index')->with('error', __('An error occurred trying to send email'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Email::destroy($id);
        return redirect()->route('emails.index')->with('message', __('Email and al its stats successfully deleted'));
    }
    
    public function unsubscribe($uuid) {
        $sent = EmailSent::where('uuid', $uuid)->first();
        $email = Email::findOrFail(array_get($sent, 'email_content_id'));
        $list = Lists::find(array_get($email, 'list_id'));
        $contact = Contact::findOrFail(array_get($sent, 'contact_id'));

        $list_unsubscribed = Unsubscribe::query()->where('list_id', array_get($list, 'id'))->where('contact_id', array_get($contact, 'id'))->count();

        $data = [
            'list_unsubscribed' => $list_unsubscribed,
            'email' => $email,
            'list' => $list,
            'contact' => $contact,
            'sent' => $sent
        ];

        return view('emails.unsubscribe')->with($data);
    }
    public function subscribeContact(Request $request){
        $contact = Contact::findOrFail(array_get($request, 'contact'));
        $email = Email::findOrFail(array_get($request, 'email'));
        $list = Lists::find(array_get($request, 'list'));
        $sent = EmailSent::find(array_get($request, 'sent'));
        array_set($sent, 'status', array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.SENT'));
        $sent->update();

        $from_all = $request->has('from_all');

        if ($from_all){
            $contact->unsubscribed = null;
            $contact->unsubscribed_permanently = null;
            $contact->save();
        }


        //If not resubscribing from all and just from list delete unsubscription record
        if (!$from_all) Unsubscribe::query()->where('list_id', array_get($list, 'id'))->where('contact_id', array_get($contact, 'id'))->delete();

        //Check if no unsubscription from any list, if no unsubscription untag from unsubscribed tag
        if (Unsubscribe::query()->where('contact_id', array_get($contact, 'id'))->count() === 0){
            $folder = Folder::findOrCreate('Emails', 'TAGS', $contact->tenant, true);
            $tag = Tag::findOrCreate(array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'), $folder, $contact->tenant, true);
            $contact->tags()->detach(array_get($tag, 'id'));
        }

        $tracking = new EmailTracking();
        array_set($tracking, 'tenant_id', array_get($contact, 'tenant_id'));
        array_set($tracking, 'email_sent_id', array_get($sent, 'id'));
        if(!is_null($list) && !$from_all){
            array_set($tracking, 'list_id', array_get($list, 'id'));
        }
        array_set($tracking, 'contact_id', array_get($contact, 'id'));
        array_set($tracking, 'swift_id', array_get($sent, 'swift_id'));
        array_set($tracking, 'status_timestamp', \Carbon\Carbon::now());
        array_set($tracking, 'status', array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.RESUBSCRIBED'));
        array_set($tracking, 'log_level', 'info');
        if ($from_all) array_set($tracking, 'reason', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.RESUBSCRIBED_ALL'));
        else array_set($tracking, 'reason', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.RESUBSCRIBED'));
        $tracking->save();


        $org = array_get($contact,'tenant.organization');
        if ($from_all) $message = "Successfully subscribed to $org";
        else $message = "Successfully subscribed to this mailing list";
        return redirect()->back()->with('message', $message);

    }
    public function unsubscribeContact(Request $request) {
        $contact = Contact::findOrFail(array_get($request, 'contact'));
        $email = Email::findOrFail(array_get($request, 'email'));
        $list = Lists::find(array_get($request, 'list'));
        $sent = EmailSent::find(array_get($request, 'sent'));
        array_set($sent, 'status', array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'));
        $sent->update();

        $from_all = $request->has('from_all');
        if ($from_all){
            $contact->unsubscribed = Carbon::now();
            $contact->save();
        }
        
        $unsubscription = Unsubscribe::query()->where('list_id', array_get($list, 'id'))->where('contact_id', array_get($contact, 'id'))->count();
        
        if($unsubscription && !$from_all){
            return redirect()->route('emails.unsubscribed')->with('message', 'You are already unsubscribed from this list');
        }

        $tracking = new EmailTracking();
        array_set($tracking, 'tenant_id', array_get($contact, 'tenant_id'));
        array_set($tracking, 'email_sent_id', array_get($sent, 'id'));
        if(!is_null($list) && !$from_all){
            array_set($tracking, 'list_id', array_get($list, 'id'));
        }
        array_set($tracking, 'contact_id', array_get($contact, 'id'));
        array_set($tracking, 'swift_id', array_get($sent, 'swift_id'));
        array_set($tracking, 'status_timestamp', \Carbon\Carbon::now());
        array_set($tracking, 'status', array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'));
        array_set($tracking, 'log_level', 'info');
        if ($from_all) array_set($tracking, 'reason', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.UNSUBSCRIBED_ALL'));
        else array_set($tracking, 'reason', array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.UNSUBSCRIBED'));
        $tracking->save();
        
        if (!$from_all){
            $unsubscribe = new Unsubscribe();
            array_set($unsubscribe, 'tenant_id', array_get($contact, 'tenant_id'));
            array_set($unsubscribe, 'list_id', array_get($list, 'id'));
            array_set($unsubscribe, 'contact_id', array_get($contact, 'id'));
            array_set($unsubscribe, 'email_content_id', array_get($email, 'id'));
            array_set($unsubscribe, 'email_tracking_id', array_get($tracking, 'id'));
            $unsubscribe->save();
        }
        
        $tenant = Tenant::findOrFail(array_get($contact, 'tenant_id'));
        $folder = Folder::findOrCreate('Emails', 'TAGS', $tenant, true);
        $tag = Tag::findOrCreate(array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED'), $folder, $tenant, true);
        $contact->tags()->sync(array_get($tag, 'id'), false);
        if(!is_null(array_get($email, 'track_and_tag_events'))){
            $tracks = json_decode(array_get($email, 'track_and_tag_events'), true);
            if(array_has($tracks, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED')) && !is_null(array_get($tracks, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED')))){
                $tag = Tag::findOrFail(array_get($tracks, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED')));
                $contact->tags()->sync(array_get($tag, 'id'), false);
            }
        }
        $org = array_get($tenant,'organization');
        if ($from_all) $message = "You have successfully unsubscribed from $org. You will no longer receive our news letter and other communications.";
        else $message = "You have successfully  unsubscribed from this mailing list.";
        return redirect()->back()->with('message', $message);
    }
    
    public function unsubscribed(){
        return view('emails.unsubscribed');
    }

    public function webView($uuid)
    {
        $query = $this->getQueuedEmailsQuery();
        
        $email = $query->where('email_sent.uuid', $uuid)->firstOrFail();

        $data = $this->prepareEmailData($email);

        if (array_get($email, 'email_editor_type', 'tiny') === 'topol') {
            return view('emails.send.general-topol', $data);
        } else {
            return view('emails.send.general', $data);
        }
    }
}
