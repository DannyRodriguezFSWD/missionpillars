<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormEntry;
use App\Models\Contact;
use App\Models\Address;
use App\Models\PaymentOption;
use App\Http\Requests\UpdateEntryConnection;
use Illuminate\Support\Facades\Crypt;
use App\Constants;
use Illuminate\Support\Facades\DB;

class FormEntriesController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $contact = null;
        $entry = FormEntry::findOrFail(array_get($request, 'id'));
        
        $fields = json_decode(array_get($entry, 'json'), true);
        $tags = collect($entry->form->tags)->map(function($item){
            return array_get($item, 'id');
        });
        
        if (array_key_exists('first_name', $fields)) {
            $contact = mapModel(new Contact(), $fields);
            auth()->user()->tenant->contacts()->save($contact);
            $contact->tags()->sync($tags, false);
        }

        if (array_key_exists('mailing_address_1', $fields) && $contact) {
            $address = mapModel(new Address(), $fields);
            array_set($address, 'relation_id', array_get($contact, 'id'));
            array_set($address, 'relation_type', get_class($contact));
            
            auth()->user()->tenant->addresses()->save($address);
        }
        
        if (array_key_exists('card_number', $fields) && $contact) {
            $expiration = array_get($fields, 'year') .'-'. array_get($fields, 'month') .'-'. '01';
            $payment = mapModel(new PaymentOption(), $fields);
            array_set($payment, 'card_expiration', $expiration);
            array_set($payment, 'contact_id', array_get($contact, 'id'));
            auth()->user()->tenant->paymentOptions()->save($payment);
        }
        
        if ($contact) {
            $relationship = array_get($request, 'relationship', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT'));
            $contact->formEntries()->sync([
                array_get($entry, 'id') => ['relationship' => $relationship]
            ], false);
            //return redirect()->route('contacts.edit', ['id' => array_get($contact, 'id')]);
            return redirect()->back();
        }
        
        return redirect()->route('contacts.create', ['id' => array_get($contact, 'id'), 'entry_id' => array_get($entry, 'id')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $entry = FormEntry::findOrFail($id);
        $form = $entry->form;
        
        $json = array_get($entry, 'json');
        $fields = json_decode($json, true);

        $match = [];
        if(array_has($fields, 'email_1')){
            $match = Contact::where([
                ['email_1', '=', array_get($fields, 'email_1', '@')]
            ])->orWhere(function($q) use($fields){
                $q->where('first_name', array_get($fields, 'first_name'))
                    ->where('last_name', array_get($fields, 'last_name'));
            })->get();
        }

        if (array_has($fields, 'payment')) {
            $flat = array_flatten(array_get($fields, 'payment', []));
            array_set($fields, 'payment', $flat);
            $json = json_encode($fields);
        }
        
        $contact = Contact::whereHas('formEntries', function($q) use($entry){
            $q->whereIn('contact_entry.relationship', [array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT'), array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER_AND_FORM_CONTACT')])
                ->where('contact_entry.form_entry_id', array_get($entry, 'id'));
        })->first();

        $payer = Contact::whereHas('formEntries', function($q) use($entry){
            $q->whereIn('contact_entry.relationship', [array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER'), array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'PAYER_AND_FORM_CONTACT')])
                ->where('contact_entry.form_entry_id', array_get($entry, 'id'));
        })->first();
        
        $transaction = array_get($entry, 'transaction');
        $split = null;
        if(!is_null($transaction)){
            $split = $transaction->splits->where('type', 'purchase')->first();
        }

        $data = [
            'form' => $form,
            'entry' => $entry,
            'json' => $json,
            'split' => $split,
            'fields' => $fields,
            'contact' => $contact,
            'payer' => $payer,
            'match' => $match
        ];
        
        return view('entries.show')->with($data);
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
    public function update(UpdateEntryConnection $request, $id) {
        $referrer = $request->headers->get('referer');
        
        try {
            $contactId = Crypt::decrypt(array_get($request, 'cid'));
            $contact = Contact::findOrFail($contactId);
            $entry = FormEntry::findOrFail($id);
            $fields = json_decode(array_get($entry, 'json'), true);

            if (array_key_exists('mailing_address_1', $fields) && $contact) {
                $address = mapModel(new Address(), $fields);
                array_set($address, 'relation_id', array_get($contact, 'id'));
                array_set($address, 'relation_type', Contact::class);
                auth()->user()->tenant->addresses()->save($address);
            }
            
            if ($contact) {
                $contact->tags()->sync(array_get($entry->getRelationTypeInstance, 'tagInstance.id'), false);
                $contact->tags()->sync(array_get($entry->form, 'tagInstance.id'), false);
                $entry->update();

                $relationship = array_get($request, 'relationship', array_get(Constants::CONTACT_FORM_RELATIONSHIPS, 'FORM_CONTACT'));
                $existing_entry = $contact->formEntries()->where('form_entry_id', array_get($entry, 'id'))->first();
                $existing_relationship = array_get($existing_entry, 'pivot.relationship');

                $action = array_get($request, 'link_action', 'link');                
                if($action == 'link'){
                    DB::table('contact_entry')->insert([
                        [
                            'form_entry_id' => array_get($entry, 'id'),
                            'contact_id' => array_get($contact, 'id'),
                            'relationship' => $relationship
                        ]
                    ]);
                }
                else if($action == 'link_and_update'){
                    DB::table('contact_entry')->insert([
                        [
                            'form_entry_id' => array_get($entry, 'id'),
                            'contact_id' => array_get($contact, 'id'),
                            'relationship' => $relationship
                        ]
                    ]);

                    foreach($fields as $key => $value){
                        if( !empty($value) && in_array($key, $contact->getAttributes(false)) ){
                            array_set($contact, $key, $value);
                        }
                    }
                    $contact->update();
                }
                else if(array_get($request, 'link_action') == 'unlink'){
                    DB::table('contact_entry')->where([
                        ['form_entry_id', '=', array_get($entry, 'id')],
                        ['contact_id', '=', array_get($contact, 'id')],
                        ['relationship', '=', $relationship]
                    ])->delete();
                }
            }
            return redirect()->back();
        } catch (\Illuminate\Contracts\Encryption\DecryptException $ex) {
            return redirect()->route('cheating');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $entry = FormEntry::findOrFail($id);
        $formId = array_get($entry, 'form_id');
        $entry->delete();
        return redirect()->route('forms.show', ['id' => $formId])->with('message', 'Entry successfully deleted');
    }

}
