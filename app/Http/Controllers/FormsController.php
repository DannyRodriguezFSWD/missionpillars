<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

use App\Classes\Subdomains\TenantSubdomain;
use App\Classes\Email\EmailQueue;
use App\Constants;
use App\Models\Address;
use App\Models\AltId;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EventRegister;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Folder;
use App\Models\FormTemplate;
use App\Models\Group;
use App\Models\PaymentOption;
use App\Models\PledgeForm;
use App\Models\PurchasedTicket;
use App\Models\Purpose;
use App\Models\StatementTemplate;
use App\Models\TransactionTemplate;
use App\Http\Requests\StoreForm;
use App\Traits\AddressTrait;
use App\Traits\CountriesTrait;
use App\Traits\DocumentsTrait;
use App\Traits\Subdomains;
use App\Traits\TagsTrait;
use Intervention\Image\ImageManagerStatic as Image;

class FormsController extends Controller {

    use AddressTrait,
        Subdomains,
        CountriesTrait,
        TagsTrait,
        DocumentsTrait;

    const PERMISSION = 'crm-forms';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(auth()->check()){ // allows public routes/methods (e.g., share, submit)
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
        $this->authorize('view',Form::class);

        $data = [
            'templates' => FormTemplate::all(),
            'permissions' => array_get(auth()->user()->ability([],[
                'form-create',
                'form-view',
                'form-update',
                'form-delete',
            ],['return_type'=>'array']),'permissions'),
            'qrCodeLink' => sprintf(env('QRCODE'), '')
        ];

        return view('forms.index')->with($data);
    }

    public function paginate()
    {
        $forms = Form::whereNotNull('tenant_id')->orderBy('id', 'desc')->paginate();
        $forms->getCollection()->transform(function ($form) {
            $form->created_at = displayLocalDateTime($form->created_at)->toDateString();
            return $form;
        });
        return $forms;
    }

    private function getYearsList() {
        $now = (int) date('Y');
        $years = [];
        for ($i = 1; $i <= 8; $i++) {
            $option = new \stdClass();
            $option->label = $now;
            $option->value = $now;
            $option->selected = false;
            array_push($years, $option);
            $now += 1;
        }
        return $years;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $this->authorize('create',Form::class);
        $years = $this->getYearsList();
        $campaigns = collect(Campaign::orgOwned()->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [''=>'None']);

        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'form', null);
        array_set($data, 'countries', json_encode($this->getCountriesAsArrayObjects()));
        array_set($data, 'years', json_encode($years));
        array_set($data, 'json', '[]');
        array_set($data, 'campaigns', $campaigns);
        array_set($data, 'charts', $charts);
        array_set($data, 'tags', [array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT')]);
        array_set($data, 'manager', null);

        $content_templates = StatementTemplate::all()->map(function ($template) {
            // we need this encoded version to change tempalte preview from desktop to mobile
            $template->content_html_encoded = htmlentities($template->content);
            return $template;
        });
        array_set($data, 'content_templates', $content_templates);
        
        return view('forms.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreForm $request) {
        $form = mapModel(new Form(), $request->all());
        $form->custom_header = strip_tags($request->custom_header) ? $request->custom_header : null;
        array_set($form, 'auto_tag_form', array_get($request, 'auto_tag_form', 0));
        array_set($form, 'create_contact', array_get($request, 'create_contact', 0));
        if(!is_numeric(array_get($request, 'contact_id'))){
            array_set($form, 'contact_id', null);
        }

        array_set($form, 'name', array_get($request, 'form_name'));
        array_set($form, 'tax_deductible', array_get($request, 'tax_deductible'));
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $file->store('public/form_images');
            array_set($form, 'cover', $file->hashName());
        }

        array_set($form, 'uuid', Uuid::uuid1()->toString());
        array_set($form, 'email_type', array_get($form, 'email_type', 'default'));
        array_set($form, 'email_editor_type', array_get($form, 'email_editor_type', 'tiny'));
        auth()->user()->tenant->forms()->save($form);

        $tags = array_get($request, 'tags', []);
        array_push($tags, array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT'));
        array_push($tags, array_get($form, 'tagInstance.id'));
        $form->tags()->sync($tags);

        return response()->json(['message' => 'Form Created!', 'redirect' => route('forms.edit', ['id' => array_get($form, 'id')])]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $form = Form::findOrFail($id);
        $this->authorize('show',$form);
        $tab = array_get($request, 'tab');
        $entries = array_get($form, 'entries');

        $linked = FormEntry::whereHas('contact', function($q){
            $q->whereIn('contact_entry.relationship', [array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT'), array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER_AND_FORM_CONTACT')]);
        })->whereIn('id', array_pluck($entries, 'id'))->orderBy('id', 'desc');
        
        $totalConnected = $linked->count();

        $unlinked = FormEntry::whereIn('id', array_pluck($entries, 'id'))->doesntHave('contact')
            ->orWhereHas('contact', function($q) use($entries, $linked){
                $q->where('contact_entry.relationship', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER'))
                ->whereIn('contact_entry.form_entry_id', array_pluck($entries, 'id'))
                ->whereNotIn('contact_entry.form_entry_id', array_pluck($linked, 'id'));
            })->orderBy('id', 'desc');
            
        $totalUnLinked = $unlinked->count();
            
        $data = [
            'form' => $form,
            'total' => $totalUnLinked,
            'unlinked' => $unlinked->paginate(15, ['*'], 'unlinked'),
            'linked' => $linked->paginate(15, ['*'], 'linked'),
            'totalConnected' => $totalConnected,
            'tab' => $tab
        ];

        return view('forms.show')->with($data);
    }

    /**
     * Dipslay a form
     * NOTE: this is a public page (not logged in)
     * @param  integer $id      [description]
     * @param  Request $request [description]
     * @return Illuminate\View\View           [description]
     */
    public function share($id, Request $request) {
        $params = $request->only(['cid', 'ticket_id']);

        $tenant = TenantSubdomain::getTenant($request);
        if (!$tenant) {
            abort(404);
        }

        $form = Form::withoutGlobalScopes()->where([
                    ['uuid', '=', $id],
                    ['tenant_id', '=', array_get($tenant, 'id')]
                ])->first();

        if (!$form) {
            abort(404);
        }
        
        $data = [
            'contact' => Contact::find(array_get($request,'cid')),
            'tenant' => $tenant,
            'form' => $form,
            'params' => http_build_query($params),
            'hasProfileImage' => $form->has_profile_image,
            'requiresProfileImage' => $form->requires_profile_image,
            'imagePath' => null,
            'showRemoveButton' => false
        ];
        return view('forms.public')->with($data);
    }

    /**
     * Submit a form
     * NOTE: this is a public page (not logged in)
     * @param  integer $id
     * @param  Request $request
     * @return Illuminate\View\View
     */
    public function submit($id, Request $request) {
        \App\Classes\Redirections::store($request);
        $tenant = TenantSubdomain::getTenant($request);
        $form = Form::where([['uuid', '=', $id], ['tenant_id', '=', array_get($tenant, 'id')]])->first();
        if (is_null($tenant) || is_null($form)) {
            abort(404);
        }

        $contact = null;
        //comes from event or group singn up
        if (array_has($request, 'cid') && !is_null(array_get($request, 'cid'))) {
            $contact = Contact::find(array_get($request, 'cid'));
        }
        //comes from single form

        if(is_null($contact)){
            // Create contact from form fields
            if(array_get($form, 'create_contact') ){
                if (!is_null(array_get($request, 'email_1'))) {
                    $contact = Contact::where([
                        ['email_1', '=', array_get($request, 'email_1', '@@@@')],
                        ['tenant_id', '=', array_get($tenant, 'id')]
                    ])->first();

                    if( is_null($contact) ){
                        $contact = mapModel(new Contact(), $request->all());
                        array_set($contact, 'tenant_id', array_get($tenant, 'id'));
                        $contact->save();
                    }
                    else{
                        foreach($request->all() as $key => $value){
                            if( !empty($value) && in_array($key, $contact->getAttributes(false)) ){
                                array_set($contact, $key, $value);
                            }
                        }
                        $contact->update();
                    }
                }
            }
        }

        if ($contact) {
            $this->findOrCreateValidAddress($contact, $request->all());
            
            if ($request->hasFile('profile_image')) {
                $this->storeProfileImage($contact, $request);
            }
        }

        $redirect = \App\Classes\Redirections::get();
        $entity = \App\Classes\Redirections::getEntityFromSession($request);
        $fields = $request->except(['_token', 'uid', 'cid', 'ticket_id', 'start_url', 'next_url', 'removeCoverImage']);
        
        $entry = new FormEntry();
        array_set($entry, 'form_id', array_get($form, 'id'));
        array_set($entry, 'tenant_id', array_get($tenant, 'id'));
        array_set($entry, 'json', json_encode($fields));
        array_set($entry, 'relation_id', array_get($entity, 'id'));
        array_set($entry, 'relation_type', get_class($entity));
        $entry->save();

        if ($request->allFiles()) {
            $this->storeUploadedFiles($entry, $request->allFiles(), array_get($tenant, 'id'));
        }
        
        // Update entry and contact's entries and tags if contact is available
        if (!is_null($contact)) {
            array_set($entry, 'contact_id', array_get($contact, 'id'));
            $entry->save();
            // Doing both as the following can be overwritten (e.g., by events)
            $contact->formEntries()->sync([
                array_get($entry, 'id') => ['relationship' => array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT')]
            ], false);
            $contact->tags()->sync(array_get($form, 'tags'), false);
        }

        $emailType = array_get($form, 'email_type', 'default');
        
        // send email confirmation form contact exists
        if(!is_null($contact) && $emailType !== 'no_email'){
            $params = [
                'manager' => null,
                'contact' => $contact,
                'tenant' => $tenant,
                'form' => $form,
                'entry' => $entry,
                'total' => array_get($request, 'total', 0)
            ];
            
            if ($emailType === 'custom' && !empty(array_get($form, 'email_content'))) {
                $content = array_get($form, 'email_content');
                $editorType = array_get($form, 'email_editor_type');
                $subject = array_get($form, 'email_subject');
            } else {
                $content = view('emails.send.forms.submit', $params)->render();
                $editorType = 'tiny';
                $subject = 'Form submission';
            }
            
            $args = [
                'from_name' => array_get($tenant, 'organization'),
                'from_email' => array_get($tenant, 'email'),
                'subject' => $subject,
                'content' => $content,
                'model' => $contact,
                'queued_by' => 'forms.submit',
                'email_editor_type' => $editorType
            ];
            EmailQueue::set($contact, $args);
        }

        // Handle 'manager' notification
        $manager = array_get($form, 'contact');
        if( !is_null($manager) ){
            $params = [
                'manager' => $manager,
                'contact' => $contact,
                'tenant' => $tenant,
                'form' => $form,
                'entry' => $entry,
                'total' => array_get($request, 'total', 0)
            ];
            $content = view('emails.send.forms.submit', $params)->render();
            $args = [
                'from_name' => array_get($tenant, 'organization'),
                'from_email' => array_get($tenant, 'email'),
                'subject' => 'Form submission',
                'content' => $content,
                'model' => $manager,
                'queued_by' => 'forms.submit'
            ];
            EmailQueue::set($manager, $args);
        }

        if( array_get($request, 'total', 0) > 0 ){
            $data = [
                'id' => $id,
                'contact_id' => array_get($contact, 'id'),
                'entry_id' => array_get($entry, 'id'),
                'total' => array_get($request, 'total')
            ];
            return redirect()->route('forms.public.payment', $data);
        }

        // Look for internal redirects
        if(strpos($redirect, 'calendar') !== false || strpos($redirect, 'events') !== false || strpos($redirect, 'checkin') !== false && !is_null($contact)){
            $ticket_id = array_get($request, 'ticket_id', 0);
            //$ticket = $ticket_id == 0 ? $entity->tickets->first() : PurchasedTicket::findOrFail(array_get($request, 'ticket_id'));
            if($ticket_id == 0 && !is_null(array_get($entity, 'tickets'))){
                $ticket = $entity->tickets->first();
            }
            else {
                $ticket = PurchasedTicket::find(array_get($request, 'ticket_id'));
            }

            if(!is_null($ticket)){
                $registry = EventRegister::find(array_get($ticket, 'calendar_event_contact_register_id'));

                if (!empty($registry)) {
                    // attach the form entry to all tickets instead of only the first one if they buy more than one ticket at once
                    $registry->tickets()->update([
                        'form_filled' => true,
                        'form_entry_id' => array_get($entry, 'id')
                    ]);
                } else {
                    array_set($ticket, 'form_filled', true);
                    array_set($ticket, 'form_entry_id', array_get($entry, 'id'));
                    $ticket->update();
                }
            }

            //Prioritize Forms Custom landing page
            $custom_redirect = $this->redirect($form);
            if($custom_redirect !== false){ return redirect($custom_redirect); }

            $custom_redirect = $this->redirect($entity);
            if($custom_redirect !== false){ return redirect($custom_redirect); }

            //auto checkin for free with attached form
            if(array_get($entity, 'template.allow_auto_check_in') && !array_get($entity, 'template.allow_reserve_tickets') || strpos($redirect, 'checkin') !== false){
                return redirect($redirect)->with('message', __('Complete. Thank You!'));
            }

            $data = ['redirect' => $redirect];
            return redirect()->route('events.finish.screen', ['id' => array_get($ticket, 'registry.id', 0)])->with($data);
        }

        // Still here? look for user defined redirect or end normally

        $custom_redirect = $this->redirect($form);
        if($custom_redirect !== false){ return redirect($custom_redirect); }

        return redirect($redirect)->with('message', __('Complete. Thank You!'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $form = Form::findOrFail($id);
        $form->content = array_get($form, 'email_content');
        $this->authorize('update',$form);
        $years = $this->getYearsList();
        $campaigns = collect(Campaign::orgOwned()->receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::receivesDonations()->get())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [''=>'None']);

        $tags = collect($form->tags)->map(function($item) {
                    return array_get($item, 'id');
                }, [])->toArray();

        $manager = null;
        if(!is_null(array_get($form, 'contact_id'))){
            $manager = array_get($form, 'contact.first_name').' '.array_get($form, 'contact.last_name').' ('.array_get($form, 'contact.email_1').')';
        }

        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'form', $form);
        array_set($data, 'countries', json_encode($this->getCountriesAsArrayObjects()));
        array_set($data, 'years', json_encode($years));
        array_set($data, 'json', array_get($form, 'json', '[]'));
        array_set($data, 'campaigns', $campaigns);
        array_set($data, 'charts', $charts);
        array_set($data, 'tags', $tags);
        array_set($data, 'manager', $manager);

        $content_templates = StatementTemplate::all()->map(function ($template) {
            // we need this encoded version to change tempalte preview from desktop to mobile
            $template->content_html_encoded = htmlentities($template->content);
            return $template;
        });
        array_set($data, 'content_templates', $content_templates);
        
        return view('forms.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $form = Form::findOrFail($id);
        $this->authorize('update',$form);
        if ((int) array_get($request, 'purpose_id') == 0) {
            array_set($request, 'purpose_id', null);
            array_set($request, 'campaign_id', null);
        }

        array_set($form, 'accept_payments', false);
        array_set($form, 'dont_allow_amount_change', false);
        array_set($form, 'allow_amount_in_url', false);
        array_set($form, 'do_not_show_form_name', false);

        mapModel($form, $request->all());
        array_set($form, 'auto_tag_form', array_get($request, 'auto_tag_form', 0));
        array_set($form, 'create_contact', array_get($request, 'create_contact', 0));

        array_set($form, 'name', array_get($request, 'form_name'));
        array_set($form, 'tax_deductible', array_get($request, 'tax_deductible'));

        if(array_get($request, 'contact_id') == 'null' || empty(array_get($request, 'contact_id'))){
            array_set($form, 'contact_id', null);
        }
        if ($request->has('removeCoverImage') && !empty($form->cover)) {
            checkAndDeleteFile(storage_path('app/public/form_images/' . $form->cover));
            $form->update(['cover' => null]);
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            $file->store('public/form_images');
            array_set($form, 'cover', $file->hashName());
        }
        $form->custom_header = strip_tags($request->custom_header) ? $request->custom_header : null;

        if (!$form->update()) abort(500);

        $tags = array_get($request, 'tags', []);
        $form->tags()->sync($tags);
        return response()->json(['message' => __('Form successfully updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $form = Form::findOrFail($id);
        $this->authorize('delete',$form);
        Form::destroy($id);
    }

    /**
     * TODO is this used? there is a dd making this show JSON for /forms/{id}/tags. Commenting it out produces an error in the view
     * Note: this appears to be a public method handler
     */
    public function tags($id, Request $request) {
        $form = Form::findOrFail($id);
        $root = Folder::find(array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        $data = $this->getDataTree($root, array_get($root, 'id'));
        array_set($data, 'form', $form);
        dd(array_get($form, 'tags'));
        $tags = collect($form->tags)->map(function($item) {
                    return array_get($item, 'id');
                }, [])->toArray();
        array_push($tags, array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT'));
        array_push($tags, array_get($form, 'tagInstance.id'));

        array_set($data, 'tags', $tags);

        return view('forms.tags')->with($data);
    }

    /**
     * TODO see note on ::tags, this is effectively unsued because of that
     */
    public function formTags($id, Request $request) {
        if ($request->has('tags')) {
            $form = Form::findOrFail($id);
            $tags = array_get($request, 'tags');

            if (!in_array(array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT'), $tags)) {
                array_push($tags, array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT'));
            }

            if (!in_array(array_get(Constants::TAG_SYSTEM, 'TAGS.FORM_RESPONDENT'), $tags)) {
                array_push($tags, array_get($form, 'tagInstance.id'));
            }

            $form->tags()->sync($tags);
            return redirect()->route('forms.tags', ['id' => $id])->with('message', __('Form tagged successfully'));
        }

        return redirect()->route('forms.tags', ['id' => $id])->with('error', __('Select 1 tag at least'));
    }

    public function templates($id, Request $request) {
        $template = FormTemplate::findOrFail($id);
        $json = array_get($template, 'json', []);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [''=>'None']);

        $data = [
            'years' => json_encode($years = $this->getYearsList()),
            'countries' => json_encode($this->getCountriesAsArrayObjects()),
            'template' => $template,
            'json' => $json,
            'campaigns' => $campaigns,
            'charts' => $charts
        ];
        return view('forms.templates')->with($data);
    }

    /**
     * Handles payment for form payment
     * NOTE: this is a public page (not logged in)
     * @param  [integer]  $id
     * @param  Request $request
     * @return Illuminate\View\View
     */
    public function payment($id, Request $request) {
        $tenant = TenantSubdomain::getTenant($request);
        $form = Form::where('uuid', $id)->first();
        $entry = FormEntry::where('id', array_get($request, 'entry_id'))->first();
        $contact = array_get($entry, 'contact');

        $params = [
            'form' => array_get($form, 'id'),
            'contact' => array_get($contact, 'id'),
            'entry' => array_get($entry, 'id'),
            'campaign' => str_random(),
            'chart' => str_random()
        ];

        $altId = array_get($form, 'campaign.getAltIds.0.alt_id');
        if (is_null($altId)) {
            $altId = array_get($form, 'chartOfAccount.getAltIds.0.alt_id');
        }

        if (is_null($altId)) {
            $altId = array_get($tenant, 'altId.alt_id');
            array_set($params, 'campaign', array_get($form, 'campaign_id'));
            array_set($params, 'chart', array_get($form, 'purpose_id'));
        }

        $code = implode('-', $params);
        $url = route('forms.finish', ['id' => array_get($form, 'uuid'), 'code' => $code]);

        $data = [
            'tenant' => $tenant,
            'id' => $id,
            'form' => $form,
            'contact' => $contact,
            'type' => array_get($form, 'tax_deductible', 0) === 1 ? 'donation' : 'purchase',
            'code' => $code,
            'total' => array_get($request, 'total'),
            'alt_id' => $altId,
            'url' => $url,
            'event' => null
        ];

        return view('events.payment')->with($data);
    }


    /**
     * Finishes payment process and send receipt to contact
     * @param type $id
     * @param Request $request
     */
    public function finish($id, $xcode, Request $request) {
        $code = explode('-', $xcode);
        /*
          $code = [
          0 => form_id,
          1 => contact_id,
          2 => entry_id,
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
            $entry = FormEntry::findOrFail($code[2]);
            array_set($entry, 'transaction_id', array_get($template, 'transactions.0.id'));
            $entry->update();
            $form = array_get($entry, 'getRelationTypeInstance');

            $manager = array_get($form, 'contact');
            $contact = array_get($entry, 'transaction.contact');
            $tenant = array_get($entry, 'tenant');
            $params = [
                'contact' => $contact,
                'tenant' => $tenant,
                'form' => $form,
                'entry' => $entry,
                'entry_url' => sprintf(env('APP_DOMAIN'), array_get($tenant, 'subdomain')).'crm/entries/'.array_get($entry, 'id')
            ];

            $frm = $contact->formEntries()->where([
                ['contact_entry.contact_id', '=', array_get($contact, 'id')],
                ['form_entry_id', '=', array_get($entry, 'id')],
                ['relationship', '=', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER')]
            ])->first();

            if(is_null($frm)){//if null then create record else update relationship
                $relationship = array_get($contact, 'id') === array_get($entry, 'contact_id') ? array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER_AND_FORM_CONTACT') : array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER');
                $contact->formEntries()->sync([
                    array_get($entry, 'id') => ['relationship' => $relationship]
                ], false);
            }
            /*
            else{
                DB::table('contact_entry')->where([
                    ['contact_id', '=', array_get($contact, 'id')],
                    ['form_entry_id', '=', array_get($entry, 'id')]
                ])->delete();
                $contact->formEntries()->sync([
                    array_get($entry, 'id') => ['relationship' => array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER_AND_FORM_CONTACT')]
                ], false);
            }
            */
            if( !is_null($contact) ){//send email
                array_set($params, 'manager', null);
                $content = view('emails.send.forms.paid', $params)->render();
                $args = [
                    'from_name' => array_get($tenant, 'organization'),
                    'from_email' => array_get($tenant, 'email'),
                    'subject' => 'Payment Receipt',
                    'content' => $content,
                    'model' => $contact,
                    'queued_by' => 'forms.finish'
                ];
                EmailQueue::set($contact, $args);//email for contact
            }

            if( !is_null($manager) ){
                array_set($params, 'manager', $manager);
                $content = view('emails.send.forms.paid', $params)->render();

                $args = [
                    'from_name' => array_get($tenant, 'organization'),
                    'from_email' => array_get($tenant, 'email'),
                    'subject' => 'Payment Receipt',
                    'content' => $content,
                    'model' => $manager,
                    'queued_by' => 'forms.finish',
                ];
                EmailQueue::set($manager, $args);//email for manager?
            }

            $custom_redirect = $this->redirect($form);
            if($custom_redirect !== false){
                return redirect($custom_redirect);
            }
            return redirect()->route('forms.finish.screen', ['id' => array_get($form, 'uuid'), 'contact_id' => array_get($entry, 'contact.id')]);
        }
        abort(500);
    }

    public function export($id, Request $request) {
        $form = Form::findOrFail($id);
        $headers = [];
        $from = null;
        $to = null;

        if( array_has($request, 'export') && array_get($request, 'export') == 1 ){
            $entries = $form->entries()->with('contact.tags')->orderBy('id', 'desc')->get();
        }
        else if ( array_has($request, 'export') && array_get($request, 'export') == 0 ){
            $from = \Carbon\Carbon::parse(array_get($request, 'from', date('Y-m-d')));
            $to = \Carbon\Carbon::parse(array_get($request, 'to', date('Y-m-d')));
            $local_time = displayLocalDateTime($from);
            $utc_local_date_time = new Carbon($local_time->toDateTimeString());

            $diff = $from->diffInHours($utc_local_date_time);

            $from->startOfDay()->addHours($diff);
            $to->endOfDay()->addHours($diff);

            $entries = $form->entries()->with('contact.tags')->whereBetween('created_at', [$from, $to])->get();
        }
        else{
            $entries = $form->entries()->with('contact.tags')->orderBy('id', 'desc')->paginate();
        }
        if( !is_null($form) ){
            $array = json_decode(array_get($form, 'json', '{}'), true);
            foreach ($array as $item){
                if( !in_array(array_get($item, 'type'), ['header', 'paragraph', 'file']) ){
                    $header = [
                        'title' => implode('_', ['form', str_slug(array_get($item, 'label'), '_')]),
                        'name' => array_get($item, 'name')
                    ];
                    array_push($headers, $header);
                }
            }
        }
        $str_fields = implode('|', array_pluck($entries, 'json'));
        $has_total_field = strpos($str_fields, 'total');
        if(!in_array('total', $headers) && $has_total_field !== false && array_get($form, 'accept_payments')){
            array_push($headers, [
                'title' => implode('_', ['form', 'total']),
                'name' => 'total'
            ]);
        }
        $jsonform = collect(json_decode($form->json));
        //store input names on $jsonformnames stripped []
        $jsonformnames = $jsonform->map(function($field){
            if (!isset($field->name)) return '';
            return str_replace('[]','',$field->name);
        });
        $entries_2 = array();

        // process each form entry, handling special cases
        foreach ($entries as $entry){
            $json = json_decode($entry->json);
            $temp_entry = [];
            $temp_entry['created_at'] = displayLocalDateTime($entry->created_at)->toDayDateTimeString();

            // Check each form field/value pair for special cases: total field, multiple values, country field
            foreach ($json as $key => $value){
                if ($key == 'total'){
                    array_push($temp_entry,['value' => $value, 'key' => $key]);
                }
                else if (is_array($value) && $jsonform->where('name',$key)->count() != 1) {
                    // multiple values, add each to temp_entry array
                    foreach ($value as $val){
                        array_push($temp_entry,['value' => $val, 'key' => $key]);
                    }
                }
                else if ($key == 'country'){
                    array_push($temp_entry,['value' => Country::find($value)->name, 'key' => $key]);
                } 
                else array_push($temp_entry,['value' => $value, 'key' => $key]);
            }

            $temp_entry = collect($temp_entry);

            // Add blank values for form values that are not set
            foreach ($jsonformnames as $name){
                if (!$temp_entry->where('key',$name)->count()) $temp_entry->push(['value' => '', 'key' => $name]);
            }
            
            if (array_has($request, 'export') && array_get($request, 'export') == 1) {
                $temp_entry['tags'] = array_get($entry, 'contact.0.all_tags');
            }
            
            array_push($entries_2,$temp_entry);
        }
        $headers = collect($headers);
        
        // remove file uploads from export
        foreach ($entries_2 as &$entry) {
            foreach ($entry as $key => $item) {
                if (is_array($item)) {
                    $itemType = $jsonform->where('name', array_get($item, 'key'))->first();
                    if ($itemType && $itemType->type === 'file') {
                        unset($entry[$key]);
                    }
                }
            }
        }
        
        $data = [
            'form' => $form,
            'entries' => $entries,
            'entries2' => $entries_2,
            'headers' => $headers,
            'from' => $from,
            'to' => $to,
            'export' => array_get($request, 'export')
        ];

        if(array_has($request, 'export')){
            $filename = substr(str_slug(array_get($form, 'name'), '_'), 0, 28);
            array_set($data, 'filename', $filename);
            Excel::create($filename, function($excel) use ($data) {
                $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                    $sheet->setOrientation('portrait');
                    $sheet->loadView('forms.excel', $data);
                });
            })->download('xlsx');
        }

        return view('forms.export')->with($data);
    }

    public function excel($id, Request $request) {
        abort(404);
        $form = Form::findOrFail($id);
        $from = null;
        $to = null;
        if( (int)array_get($request, 'export') === 1 ){
            $entries = $form->entries;
        }
        else{
            $from = \Carbon\Carbon::parse(array_get($request, 'from', date('Y-m-d')));
            $to = \Carbon\Carbon::parse(array_get($request, 'to', date('Y-m-d')));
            $entries = $form->entries()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])->get();
        }

        $headers = [];
        if( !is_null($form) ){
            $array = json_decode(array_get($form, 'json'), true);
            foreach ($array as $item){
                if( !in_array(array_get($item, 'type'), ['header']) ){
                    array_push($headers, implode('_', ['form', str_slug(array_get($item, 'label'), '_')]));
                }
            }
        }

        $str_fields = implode('|', array_pluck($entries, 'json'));
        $has_total_field = strpos($str_fields, 'total');
        if(!in_array('total', $headers) && $has_total_field !== false && array_get($form, 'accept_payments')){
            array_push($headers, implode('_', ['form', 'total']));
        }

        $filename = substr(str_slug(array_get($form, 'name'), '_'), 0, 28);
        $data = [
            'form' => $form,
            'entries' => $entries,
            'headers' => $headers,
            'from' => $from,
            'to' => $to,
            'filename' => $filename
        ];
        //dd($headers);
        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('forms.excel', $data);
            });
        })->download('xlsx');
    }

    public function finishScreen($id, Request $request){
        $form = Form::where('uuid', $id)->first();
        $contact = Contact::find(array_get($request, 'contact_id'));

        $redirect = \App\Classes\Redirections::get();
        $data = [
            'form' => $form,
            'contact' => $contact,
            'redirect' => $redirect
        ];

        return view('forms.payment-finished')->with($data);
    }

    public function redirect($entity) {
        if(get_class($entity) == CalendarEventTemplateSplit::class){
            if(!empty(array_get($entity, 'template.custom_landing_page')) && filter_var(array_get($entity, 'template.custom_landing_page'), FILTER_VALIDATE_URL)){
                return array_get($entity, 'template.custom_landing_page');
            }

            if(!empty(array_get($entity, 'template.custom_landing_page')) && !filter_var(array_get($entity, 'template.custom_landing_page'), FILTER_VALIDATE_URL)){
                return 'http://'.array_get($entity, 'template.custom_landing_page');
            }
        }

        if(get_class($entity) == Form::class){
            if(!empty(array_get($entity, 'custom_landing_page')) && filter_var(array_get($entity, 'custom_landing_page'), FILTER_VALIDATE_URL)){
                return array_get($entity, 'custom_landing_page');
            }

            if(!empty(array_get($entity, 'custom_landing_page')) && !filter_var(array_get($entity, 'custom_landing_page'), FILTER_VALIDATE_URL)){
                return 'http://'.array_get($entity, 'custom_landing_page');
            }
        }

        return false;
    }

    public function iframe(Request $request, $id){
        $tenant = TenantSubdomain::getTenant($request);
        if (!$tenant) {
            abort(404);
        }
        $randomDivId = rand(999,999999).'_mpIframe';

        $form = Form::withoutGlobalScopes()->where([
            ['uuid', '=', $id],
            ['tenant_id', '=', array_get($tenant, 'id')]
        ])->firstOrFail();
        $route = route('forms.share', ['id' => $id]);
        $frameHeight = array_get((array) json_decode(array_get($form,'custom_style')),'frameHeight','700');
        return response('
                    (function(){
                        function loadFrame() {
                        let div = document.createElement("div");
                        div.setAttribute("id","'.$randomDivId.'");
                        document.currentScript.insertAdjacentHTML("beforebegin",div.outerHTML);
                        let iframe = document.createElement("iframe");
                        iframe.setAttribute("src", "'.$route.'");
                        iframe.setAttribute("style","min-width: 100%; height: '.$frameHeight.'px; border: 0");
                        iframe.setAttribute("allowTransparency","true");
                        document.getElementById("'.$randomDivId.'").appendChild(iframe);
                    }
                    loadFrame()
                    })()
                ')->header('Content-Type', 'application/javascript');
    }

    public function storeProfileImage(Contact $contact, Request $request)
    {
        $image = $request->file('profile_image');
        $imageResize = Image::make($image)->resize(400, 400);
        
        if (env('AWS_ENABLED')) {
            if (!empty($contact->profile_image)) {
                Storage::disk('s3')->delete($contact->profile_image);
            }
            
            Storage::disk('s3')->put('profile_images/'.$image->hashName(), $imageResize->stream(), 'public');
            array_set($contact, 'profile_image', 'profile_images/'.$image->hashName());
        } else {
            if (!empty($contact->profile_image)) {
                checkAndDeleteFile(storage_path('app/public/contacts/' . $contact->profile_image));
            }
            
            $imageResize->save(storage_path('app/public/contacts/'.$image->hashName()));
            array_set($contact, 'profile_image', $image->hashName());
        }
        
        $contact->update();
    }
    
    public function storeUploadedFiles(FormEntry $entry, $files, $tenantId)
    {
        $fields = json_decode(array_get($entry, 'json'), true);
        
        foreach ($files as $key => $file) {
            $document = $this->storeDocument($file, 'form_entries', false, false, 0, $tenantId);
            array_set($fields, $key, array_get($document, 'uuid'));
            array_set($document, 'relation_id', array_get($entry, 'id'));
            array_set($document, 'relation_type', get_class($entry));
            $document->update();
        }
        
        array_set($entry, 'json', json_encode($fields));
        $entry->update();
    }
    
    public function duplicate($id)
    {
        $form = Form::findOrFail($id);
        $newForm = $form->replicate();
        $newForm->name = $form->name.' - duplicate';
        $newForm->uuid = Uuid::uuid1()->toString();
        $newForm->created_at = Carbon::now();
        $newForm->save();
        
        $tags = $form->tags;
        $newForm->tags()->sync($tags);
    }
}
