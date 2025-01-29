<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Subdomains;
use App\Models\Group;
use App\Http\Requests\StoreJoin;
use App\Models\Contact;
use App\Http\Requests\JoinJoin;
use App\Models\Address;
use App\Traits\CountriesTrait;
use App\Classes\Subdomains\TenantSubdomain;
use App\Models\CalendarEvent;
use App\Models\PledgeForm;
use App\Models\EventRegister;
use App\Classes\Redirections;
use App\Models\PurchasedTicket;
use App\Classes\Events\EventSignin;
use App\Models\CalendarEventTemplateSplit;
use Carbon\Carbon;

class JoinController extends Controller {

    use Subdomains,
        CountriesTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $url = $request->getHost();
        $subdomain = $this->getSubdomain($url);
        $tenant = $this->getTenant($subdomain);
        if (!$tenant) {
            abort(404);
        }
        $groups = Group::orderBy('name')->get();
        $data = [
            'tenant' => $tenant,
            'groups' => $groups,
            'redirect' => 'join'
        ];
        return view('join.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $tenant = TenantSubdomain::getTenant($request);
        if (!$tenant) {
            abort(404);
        }

        $redirect = EventSignin::checkReservation($request);
        if(!empty($redirect)){
            return $redirect;
        }

        $registry_id = array_get($request, 'registry');
        $registry = EventRegister::withoutGlobalScopes()->where('id', $registry_id)->first();
        $split = CalendarEventTemplateSplit::withoutGlobalScopes()->where('id', array_get($registry, 'calendar_event_template_split_id'))->first();
        $event_template = CalendarEvent::withoutGlobalScopes()->where('id', array_get($split, 'calendar_event_template_id'))->first();
        $data = [
            'tenant' => $tenant,
            'registry' => $registry_id,
            'register' => $registry,
            'countries' => $this->getCountries(),
            'event_template' => $event_template
        ];
        return view('join.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJoin $request) {
        try {
            $tenant = TenantSubdomain::getTenant($request);
            if (!$tenant) {
                abort(404);
            }

            $isNewContact = false; 
            $contactSave = true;
            
            $contact = Contact::where('email_1', array_get($request, 'email_1'))->first();
                       
            if(is_null($contact)){
                $contact = new Contact();
                $isNewContact = true;
            }
            $contact = mapModel($contact, $request->all());
            array_set($contact, 'tenant_id', array_get($tenant, 'id'));
            
            if ($isNewContact) {
                $contactSave = $contact->save();
            }
            
            if ($contactSave) {
                $ticket_id = null;
                
                if(!is_null(array_get($request, 'registry'))){
                    $registry = EventRegister::find(array_get($request, 'registry'));
                    if(!is_null($registry)){
                        array_set($registry, 'contact_id', array_get($contact, 'id'));
                        $registry->update();
                        $tickets = array_get($registry, 'tickets');
                        $total = 0;
                        if(count($tickets) > 0){
                            $total = array_sum(array_pluck($tickets, 'price'));
                        }
                        
                        if($total > 0){//there is something to pay for
                            return redirect()->route('events.public.payment', [
                                'id' => array_get($registry, 'calendar_event_template_split_id'),
                                'register_id' => array_get($registry, 'id')
                            ]);
                        } else {
                            // if not paid event we might still have free tickets
                            
                            $registry->tickets()->update([
                                'temporary_hold' => false,
                                'temporary_hold_ends_at' => null,
                            ]);

                            $split = $registry->event;
                            
                            EventSignin::sendEmailToManager($split, $registry, 'Event Signup', 'events.purchase.tickets.checkout');
                            
                            if (array_get($split, 'template.allow_auto_check_in') && !array_get($split, 'template.form_must_be_filled')) {
                                $registry->tickets()->update([
                                    'checked_in' => true,
                                    'used' => true,
                                    'used_at' => Carbon::now()
                                ]);
                            }

                            $ticket_id = $registry->tickets()->count() ? $registry->tickets()->first()->id : null;
                            
                            if ($split->template->ask_whose_ticket && count($tickets) > 1) {
                                $event_title = $split->template->name;
                                $tickets = $registry->tickets;
                                return view('events.update_tickets_cred',compact('event_title','split','register','contact','tickets'));
                            }
                            
                            EventSignin::sendTicketsToContact($split, $registry, $contact);
                        }
                    }
                }

                $redirect = \App\Classes\Redirections::get();
                $form = null;
                
                if(empty($redirect)){
                    return redirect()->back()->with('message', __('Complete. Thank You!'));
                }

                $model = \App\Classes\Redirections::getEntityFromSession($request);
                
                if( get_class($model) == Group::class ){
                    $form = array_get($model, 'form');
                    $contact->groups()->sync(array_get($model, 'id'), false);
                }
                else if(get_class($model) == \App\Models\CalendarEventTemplateSplit::class){
                    $form = array_get($model, 'template.linkedForm');
                }
                
                if (!is_null($model) && !is_null(array_get($model, 'tagInstance'))) {
                    $contact->tags()->sync([array_get($model->tagInstance, 'id')], false);
                }

                if (!is_null(array_get($request, 'mailing_address_1'))) {
                    $address = mapModel(new Address(), $request->all());
                    array_set($address, 'relation_id', array_get($contact, 'id'));
                    array_set($address, 'relation_type', Contact::class);
                    $tenant->addresses()->save($address);
                }
                
                if(is_null($form) || array_get($form, 'id') == 1){//no form
                    return redirect($redirect)->with('message', __('Complete. Thank You!'));
                }

                $params = [
                    'id' => array_get($form, 'uuid'), 
                    'cid' => array_get($contact, 'id')
                ];
                
                if (!is_null($ticket_id)) {
                    $params['ticket_id'] = $ticket_id;
                }
                
                return redirect()->route('forms.share', $params)
                                    ->with('form-message', __('Thank you for joining ' . array_get($model, 'name') . ' now for step 2/2 you have to fill next form'));
            } else {
                $message = 'Something went wrong';
                $request->session()->flash('START_OVER', 'START_OVER');
                return redirect($redirect)->with('error', __($message));
            }
        } catch (Exception $ex) {
            return redirect()->route('cheating');
        }
        abort(500);
    }

    public function join($id, JoinJoin $request) {
        try {
            $group = Group::where('uuid', $id)->first();
            $contact = Contact::findOrFail(array_get($request, 'id'));
            if (!$group || !$contact) {
                abort(404);
            }
            \App\Classes\Redirections::store($request);
            $group->contacts()->sync([array_get($contact, 'id')], false);

            if (is_null(array_get($group, 'form')) || array_get($group, 'form.name') === 'None') {
                return redirect(\App\Classes\Redirections::get())->with('message', __('Thank you for joining ' . array_get($group, 'name')));
            }
            
            $params = [
                'id' => array_get($group->form, 'uuid'),
                'cid' => array_get($contact, 'id')
            ];
            
            return redirect()->route('forms.share', $params)
                            ->with('form-message', __('Thank you for joining ' . array_get($group, 'name') . ' now for step 2/2 you have to fill next form'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect()->route('cheating');
        }
        abort(500);
    }
    
    /**
     * Used by the organization contact to self join a group
     * 
     * @param type $id
     * @param Request $request
     */
    public function joinSelf($id, Request $request)
    {
        try {
            $group = Group::where('uuid', $id)->first();
            $contact = auth()->user()->contact;

            if (!$group || !$contact) {
                abort(404);
            }

            $group->contacts()->sync([array_get($contact, 'id')], false);

            if (is_null(array_get($group, 'form')) || array_get($group, 'form.name') === 'None') {
                return redirect()->route('groups.show', $group)->with('message', 'Thank you for joining '.array_get($group, 'name'));
            }

            $params = [
                'id' => array_get($group->form, 'uuid'),
                'cid' => array_get($contact, 'id')
            ];

            return redirect()->route('forms.share', $params)->with('form-message', __('Thank you for joining ' . array_get($group, 'name') . ' now for step 2/2 you have to fill next form'));
        } catch (Exception $ex) {
            return redirect()->route('cheating');
        }
        
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        $tenant = TenantSubdomain::getTenant($request);
        $group = Group::where('uuid', $id)->first();
        
        if(!$tenant || !$group){
            abort(404);
        }
        
        $data = [
            'group' => $group,
            'tenant' => $tenant
        ];
        return view('join.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
