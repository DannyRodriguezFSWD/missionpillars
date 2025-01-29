<?php

namespace App\Classes\Shared\MergeData;

use App\Constants;
use App\Models\Address;
use App\Models\AltId;
use App\Models\Contact;
use App\Models\EmailSent;
use App\Models\EventRegister;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\MergeHistory;
use App\Models\Note;
use App\Models\SMSSent;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use App\MPLog;

use DB;
/**
 *
 * @author josemiguel
 */
class MergeData {
    private $merged = [];
    
    public function __construct() {
        
    }

    private function merge($items, $contact){
        // NOTE see view-log-modal.blade.php
        foreach($items as $item){
            $history = new MergeHistory();
            array_set($history, 'relation_id', array_get($item, 'id'));
            array_set($history, 'relation_type', get_class($item));
            array_set($history, 'deleted_contact_id', array_get($item, 'contact_id', array_get($item, 'id')));
            array_set($history, 'merged_in_contact_id', array_get($contact, 'id'));
            array_set($history, 'tenant_id', array_get($contact, 'tenant_id'));
            try{
                $data = [];
                switch(get_class($item)){
                    case AltId::class:
                        array_set($history, 'deleted_contact_id', array_get($item, 'relation_id'));
                        $data = ['type' => 'altid', 'model' => $item];
                    break;
                    case Contact::class:
                        Contact::destroy(array_get($item, 'id'));
                        $this->mergeEmailAddresses($item, $contact);
                        $data = ['type' => 'contact', 'model' => $item];
                    break;
                    case Transaction::class:
                        $data = ['type' => 'transaction', 'model' => $item];
                    break;
                    case Address::class:
                        $data = ['type' => 'address', 'model' => $item];
                    break;
                    case FormEntry::class:
                        $item->form;
                        $data = ['type' => 'form', 'model' => $item];
                    break;
                    case EventRegister::class:
                        $item->event->template;
                        $data = ['type' => 'event', 'model' => $item];
                    break;
                    case Tag::class:
                        // NOTE if multiple merged contacts shared this tag, we are only recording the first
                        array_set($history, 'deleted_contact_id', $item->contacts()->where('id','!=',$contact->id)->first()->id);
                        $data = ['type' => 'tag', 'model' => $item];
                    break;
                    case Note::Class:
                        array_set($history, 'deleted_contact_id', array_get($item, 'relation_id'));
                        $data = ['type' => 'note', 'model' => $item];
                        break;
                    // not currently logging SMSSent, EmailSent
                }
                $history->save();
                if(!empty($data)){
                    array_push($this->merged, $data);
                }
            }
            catch(\Exception $ex){
                MPLog::create([
                    'event' => 'merge',
                    'code' => $ex->getCode(),
                    'message' => $ex->getMessage(),
                    'data' => json_encode($item),
                    'caller_function' => __FUNCTION__
                ]);
            };
        }
    }

    public function altids($keep, $merge = []){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            // get altids to merge and set new relation_id
            $altids = AltId::where('relation_type', Contact::class)
            ->whereIn('relation_id', $merge)->get();
            AltId::where('relation_type', Contact::class)
            ->whereIn('relation_id', $merge)->update([
                'relation_id' => array_get($contact, 'id')
            ]);
            $this->merge($altids, $contact);
            return $altids;
        }
        return []; 
    }

    public function sms($keep, $merge = []){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $sms = SMSSent::whereIn('to_contact_id', $merge)->get();
            SMSSent::whereIn('to_contact_id', $merge)->update([
                'to_contact_id' => array_get($contact, 'id')
            ]);
            $this->merge($sms, $contact);
            return $sms;
        }
        return []; 
    }

    public function emails($keep, $merge = []){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $emails = EmailSent::whereIn('contact_id', $merge)->get();
            EmailSent::whereIn('contact_id', $merge)->update([
                'contact_id' => array_get($contact, 'id')
            ]);
            $this->merge($emails, $contact);
            return $emails;
        }
        return []; 
    }

    /**
     * Merges duplicated profiles into one
     * @param Integer $keep: contact id to keep
     * @param Array $merge: contact id's array to merge
     */
    public function profiles($keep, $merge = []){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $contacts = Contact::whereIn('id', $merge)
            ->whereNotIn('id', [array_get($contact, 'id')])->get();
            $this->merge($contacts, $contact);
            return $contacts;
        }
        return [];
    }
    
    /**
     * Attempts to merge email addresses into one of the available
     * @param  Contact $mergecontact Source Contact 
     * @param  Contact $intocontact  Contact to copy emails into
     * @return Array merged email addresses indexed by spot in $intocontact
     */
    public function mergeEmailAddresses($mergecontact, $intocontact)
    {
        $emaildata = [];
        foreach ([$mergecontact->email_1, $mergecontact->email_2] as $email) {
            if ($email && $email != 'NULL') {
                if ((!$intocontact->email_1 || $intocontact->email_1 == 'NULL') && $intocontact->email_2 != $email) {
                    $intocontact->email_1 = $email;
                    $emaildata[1] = $email;
                } elseif ((!$intocontact->email_2 || $intocontact->email_2 == 'NULL') && $intocontact->email_1 != $email) {
                    // add additional email IFF space available and same email isn't already stored
                    $intocontact->email_2 = $email;
                    $emaildata[2] = $email;
                } 
            }
        }
        $intocontact->save();
        
        if (!empty($emaildata)) array_push($this->merged, ['type'=>'email_addresses', 'model'=>$emaildata]);
        return $emaildata;
    }

    /**
     * Merge tags 
     * @param  Contact  $keep              The id of the contact to merge tags to
     * @param  array   $merge             Ids of contacts to merge tags from
     * @param  boolean $onlyautogenerated Optional. If specified and true, will limit tags
     * @param  boolean $pretend           Optional. If specified and true, will only find and return tags to merge
     * @return Collection                     Tags that would be merged, excluding any existing tags
     */
    public function tags($keep, $merge = [], $includetrashed = false, $onlyautogenerated = false, $pretend = false){
        $contact = $includetrashed 
        ? Contact::withTrashed()->find($keep) 
        : Contact::find($keep);
        
        if(!is_null($contact) && !is_null($merge)){
            // get tags to merge and set new relation_id
            $existingtagids = $contact->tags()->pluck('tag_id');
            $tags = Tag::whereNotIn('id', $existingtagids)
            ->whereHas('contacts', function($q) use ($merge) {
                $q->withTrashed();
                $q->whereIn('id', $merge);
            });
            if ($onlyautogenerated) $tags->autogenerated();
            $tags = $tags->get();
            
            if (!$pretend) {
                $contact->tags()->attach($tags->pluck('id'));
                $this->merge($tags, $contact);
            }
            return $tags->toArray();
        }
        return []; 
    }

    public function transactions($keep, $merge){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $all = array_push($merge, $keep);
            $transactions = Transaction::select([
                DB::raw('count(transactions.id) as total'),
                'transactions.id',
                'transactions.transaction_template_id',
                'transactions.contact_id',
                'transactions.transaction_initiated_at',
                'transactions.status',
                'transactions.payment_option_id',
                'transactions.referrer',
                'transaction_splits.amount'
            ])->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
            ->whereIn('contact_id', $merge)
            ->whereNotIn('contact_id', [array_get($contact, 'id')])
            ->orderBy('transaction_id')
            ->groupBy([
                'transactions.status',
                'transactions.payment_option_id',
                'transactions.referrer',
                'transaction_splits.amount',
                'transactions.transaction_initiated_at'
            ])
            ->having(DB::raw('count(transactions.id)'), '=', 1)
            ->get();
            
            if(!empty($transactions)){
                $transaction_template_ids = array_pluck($transactions, 'transaction_template_id');
                $transaction_ids = array_pluck($transactions, 'id');

                Transaction::whereIn('id', $transaction_ids)->update([
                    'contact_id' => array_get($contact, 'id')
                ]);
                $this->merge($transactions, $contact);

                TransactionTemplate::whereIn('id', $transaction_template_ids)
                ->whereNotIn('contact_id', [array_get($contact, 'id')])->update([
                    'contact_id' => array_get($contact, 'id')
                ]);
                $templates = TransactionTemplate::whereIn('id', $transaction_template_ids)->get();
                $this->merge($templates, $contact);
            }
            return $transactions;
        }
        return []; 
    }

    public function forms($keep, $merge){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $forms = FormEntry::whereIn('contact_id', $merge)->get();
            FormEntry::whereIn('contact_id', $merge)->update([
                'contact_id' => array_get($contact, 'id')
            ]);
            $this->merge($forms, $contact);
            return $forms;
        }
        return []; 
    }

    public function relatives($keep, $merge){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            DB::table('contact_relatives')->whereIn('contact_id', $merge)->update([
                'contact_id' => array_get($contact, 'id')
            ]);
        }
        //this is a many to many related table so there is no model to record history
        $relatives = $contact->relatives;
        foreach($relatives as $relative){
            $data = [
                'type' => 'relationship',
                'model' => $relative
            ];
            array_push($this->merged, $data);
        }
    }

    public function addresses($keep, $merge){
        $contact = Contact::find($keep);
        $result = [];
        if(!is_null($contact) && !is_null($merge)){
            $addresses = Address::whereIn('relation_id', $merge)
                            ->where('relation_type', Contact::class)
                            ->orderBy('id', 'asc')->get();
            $contactAddresses = array_get($contact, 'addressInstance', []);
            
            if(count($contactAddresses) == 0){
                foreach($addresses as $address){
                    array_set($address, 'relation_id', array_get($contact, 'id'));
                    $address->update();
                    array_push($result, $address);
                }
            }
            else{
                foreach($contactAddresses as $contactAddress){
                    foreach($addresses as $address){
                        if( array_get($contactAddress, 'mailing_address_1') != array_get($address, 'mailing_address_1') &&
                            array_get($contactAddress, 'city') != array_get($address, 'city') &&
                            array_get($contactAddress, 'region') != array_get($address, 'region') &&
                            array_get($contactAddress, 'country') != array_get($address, 'country') &&
                            array_get($contactAddress, 'city') != array_get($address, 'postal_code')
                        ){
                            array_set($address, 'relation_id', array_get($contact, 'id'));
                            $address->update();
                            array_push($result, $address);
                        }
                    }
                }
            }
            $this->merge($addresses, $contact);
        }
        return $result;
    }

    public function events($keep, $merge){
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $registrations = EventRegister::whereIn('contact_id', $merge)->get();
            EventRegister::whereIn('contact_id', $merge)->update([
                'contact_id' => array_get($contact, 'id')
            ]);
            $this->merge($registrations, $contact);
        }
    }

    public function notes($keep, $merge)
    {
        $contact = Contact::find($keep);
        if(!is_null($contact) && !is_null($merge)){
            $notes = Note::whereIn('relation_id', $merge)
                            ->where('relation_type', Contact::class)
                            ->orderBy('id', 'asc')->get();
            
            $notesCopy = Note::whereIn('relation_id', $merge)
                            ->where('relation_type', Contact::class)
                            ->orderBy('id', 'asc')->get();
            
            foreach($notes as $note){
                array_set($note, 'relation_id', array_get($contact, 'id'));
                $note->update();
            }
           
            $this->merge($notesCopy, $contact);
        }
    }
    
    public function users($keep, $merge)
    {
        $contact = Contact::find($keep);
        
        if (!is_null($contact) && !is_null($merge) && !array_get($contact, 'user_id')) {
        
            $mergedWithUser = Contact::withTrashed()->whereIn('id', $merge)->whereNotNull('user_id')->orderBy('user_id', 'asc')->first();

            if ($mergedWithUser) {
                $userId = array_get($mergedWithUser, 'user_id');

                if ($userId) {
                    array_set($contact, 'user_id', $userId);
                    $contact->update();
                    
                    array_set($mergedWithUser, 'user_id', null);
                    $mergedWithUser->update();
                }
            }
        }
    }
    
    /**
     * Merges duplicated profiles into one and all its transactions, forms, addresses, pledges and relationships
     * @param Integer $keep: contact id to keep
     * @param Array $merge: contact id's array to merge
     */
    public function all($keep, $merge = []){
        try{
            $this->altids($keep, $merge);
            $this->tags($keep, $merge);
            $this->profiles($keep, $merge);
            $this->addresses($keep, $merge);
            $this->transactions($keep, $merge);
            $this->forms($keep, $merge);
            $this->relatives($keep, $merge);
            $this->events($keep, $merge);
            $this->sms($keep, $merge);
            $this->emails($keep, $merge);
            $this->notes($keep, $merge);
            $this->users($keep, $merge);
            
            $contact = Contact::find($keep);
            
            return ['contact' => $contact, 'data' => $this->merged];
            
        }
        catch(\Exception $ex){
            MPLog::create([
                'event' => 'merge',
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'caller_function' => __FUNCTION__
            ]);
        };
    }
    
}
