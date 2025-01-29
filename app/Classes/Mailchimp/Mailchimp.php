<?php

namespace App\Classes\Mailchimp;

use App\Http\Requests\StoreList;
use App\Models\Country;
use App\Models\Contact;
use App\Models\Integration;
/**
 * Description of Mailchimp
 *
 * @author josemiguel
 */
class Mailchimp extends MailchimpTokenAuthentication {
    
    public function __construct($token = null) {
        parent::__construct();
        if($token){
            $this->setToken($token);
        }
        else{
            $integration = Integration::where('service', 'Mailchimp')->first();
            if ($integration) {
                $token = $integration->values->where('key', 'API_KEY')->first()->value;
                $this->setToken($token);
            }
        }
    }
    
    public function createList(StoreList $request) {
        $country = Country::findOrFail(array_get($request, 'country_id'));
        $contact = [
            'company' => array_get($request, 'company'),
            'address1' => array_get($request, 'mailing_address_1'),
            'city' => array_get($request, 'city'),
            'state' => array_get($request, 'region'),
            'zip' => array_get($request, 'postal_code'),
            'country' => array_get($country, 'iso_3166_2')
        ];
        
        $campaign = $request->only(['from_name', 'from_email', 'subject', 'language']);
        
        $list = [];
        array_set($list, 'name', array_get($request, 'name'));
        array_set($list, 'contact', $contact);
        array_set($list, 'permission_reminder', array_get($request, 'permission_reminder'));
        array_set($list, 'campaign_defaults', $campaign);
        array_set($list, 'email_type_option', false);
        return $this->setList($list);
    }
    
    public function subscribeList($list, $tags) {
        if(count($tags) > 0){
            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->whereIn('tags.id', $tags)
                    ->get();
        
            $batch = [];
            foreach ($contacts as $contact) {
                $data = $this->prepareMemberData($contact);
                array_push($batch, $this->prepareBatchData($contact, array_get($list, 'mailchimp_id'), $data));
            }

            $operations = ['operations' => $batch];
            
            $response = $this->subscribeMembers($operations);
            return $response;
        }
        return null;
    }
    
    public function unsubscribeList($list, $tags) {
        if(count($tags) > 0){
            $contacts = Contact::join('contact_tag', 'contact_tag.contact_id', '=', 'contacts.id')
                    ->join('tags', 'tags.id', '=', 'contact_tag.tag_id')
                    ->whereIn('tags.id', $tags)
                    ->get();
            
            $batch = [];
            foreach ($contacts as $contact) {
                $data = $this->prepareMemberData($contact);
                array_push($batch, $this->prepareBatchData($contact, array_get($list, 'mailchimp_id'), $data, 'DELETE'));
            }
            
            $operations = ['operations' => $batch];
            $response = $this->subscribeMembers($operations);
            return $response;
        }
        return null;
    }
    
    public function updateBatchId($response, $list) {
        if( !is_null($response) && $response->getStatusCode() === 200 ){
            $batch = json_decode($response->getBody()->getContents(), true);
            array_set($list, 'mailchimp_batch_id', array_get($batch, 'id'));
            $list->update();
        }
    }
    
    public function prepareMemberData($contact) {
        $data = [];
        if (is_null(array_get($contact, 'last_name'))) {
            array_set($contact, 'last_name', "");
        }
        array_set($data, 'email_address', array_get($contact, 'email_1'));
        array_set($data, 'merge_fields.FNAME', array_get($contact, 'first_name'));
        array_set($data, 'merge_fields.LNAME', array_get($contact, 'last_name'));
        array_set($data, 'status', 'subscribed');
        return $data;
    }
    
    public function prepareBatchData($contact, $list, $data, $method = 'PUT') {
        $operations = [];
        $path = "lists/$list/members/" . md5( strtolower(array_get($contact, 'email_1')) );
        array_set($operations, 'method', $method);
        array_set($operations, 'path', $path);
        array_set($operations, 'body', json_encode($data));

        return $operations;
    }
    
}
