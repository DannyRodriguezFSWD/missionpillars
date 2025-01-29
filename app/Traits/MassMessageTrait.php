<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\Contact;
use App\Models\SMSContent;
use App\Models\Unsubscribe;
use App\Models\EmailSent;
use App\Models\SMSPhoneNumber;
use App\Models\SMSSent;
use App\Models\Tag;
use App\Models\Lists;

/**
*
* @author josemiguel
*/
trait MassMessageTrait {

    /**
     * Adds to queue only contacts with phone number
     */
    public function queue($request, $sms){
        $summary = $this->getSummary($request, $sms, true);
        
        foreach( array_get($summary, 'contacts', []) as $contact){
            $sent = new SMSSent();
            array_set($sent, 'from_contact_id', array_get(auth()->user(), 'contact.id'));
            array_set($sent, 'to_contact_id', array_get($contact, 'id'));
            array_set($sent, 'sms_content_id', array_get($sms, 'id'));
            auth()->user()->tenant->SMSSent()->save($sent);
        }
        return $summary;
    }

    public function storeSettings($settings)
    {
        if (array_get($settings, 'type') === 'sms') {
            $phone = SMSPhoneNumber::findOrFail(array_get($settings, 'message.sms_phone_number_id'));
            
            if( array_get($settings, 'id', 0) > 0 ){
                $message = SMSContent::findOrFail(array_get($settings, 'id'));
            }
            else{
                $message = new SMSContent();
            }
            array_set($message, 'tenant_id', array_get(auth()->user(), 'tenant.id'));

            if( array_get($settings, 'message.list_id') > 0 ){//0 = everyone, > 0 to list
                array_set($message, 'list_id', array_get($settings, 'message.list_id'));
            }
            
            array_set($message, 'content', array_get($settings, 'message.content'));
            array_set($message, 'sms_phone_number_from', array_get($phone, 'phone_number'));
            array_set($message, 'send_to_all', array_get($settings, 'audience.send_all'));
            array_set($message, 'send_number_of_messages', array_get($settings, 'audience.send_number_of_messages'));
            array_set($message, 'do_not_send_within_number_of_days', array_get($settings, 'audience.do_not_send_within_number_of_days'));
            array_set($message, 'track_and_tag_events', json_encode(array_get($settings, 'actions')));
            array_set($message, 'queued_by', 'MassMessageTrait.storeSettings');
            array_set($message, 'relation_id', array_get($settings, 'message.list_id', 0));
            array_set($message, 'relation_type', Lists::class);
            
            if (!array_get($settings, 'message.remove_stop_to_unsubscribe', false)) {
                array_set($message, 'add_unsubscribe_message', 1);
            }
            
            if (array_get($settings, 'message.time_scheduled', null)) {
                $timeUTC = array_get($settings, 'message.time_scheduled', null) ? setUTCDateTime(array_get($settings, 'message.time_scheduled', null)) : date('Y-m-d H:i:s');
                array_set($message, 'time_scheduled', $timeUTC);
            }
            
            if(auth()->user()->tenant->sms()->save($message)){
                $message->includeTags()->sync(array_get($settings, 'tags.include'));
                $message->excludeTags()->sync(array_get($settings, 'tags.exclude'));
                return $message;
            }
        }
    }
    
    protected function getContactLists($list) {
        // Datatable contacts
        $datatable = Lists::savedSearch()->find($list->id)
        ? \App\DataTables\ContactDataTable::createFromState($list->datatableState) : null;
        
        $include_list_tags = $datatable ? [] : array_pluck($list->inTags, 'id');
        $exclude_list_tags = $datatable ? [] : array_pluck($list->notInTags, 'id');
        
        $full_list = $datatable 
        ? Contact::whereIn('id', $datatable->getContactIdArray())->get()
        : Contact::whereHas('tags', function($query) use($datatable, $include_list_tags) {
            $query->whereIn('id', $include_list_tags);
        })->get();
        
        $exclude_list = $datatable
        ? Contact::whereNotIn('id', $datatable->getContactIdArray())->get()
        : Contact::whereHas('tags', function($query) use($exclude_list_tags) {
            $query->whereIn('id', $exclude_list_tags);
        })->get();
        
        $final_list = array_diff(array_pluck($full_list, 'id'), array_pluck($exclude_list, 'id'));
        
        return compact('full_list','exclude_list','final_list');
    }

    /**
     * gets preview summary
     */
    public function getSummary($request, $sms = null, $queue = false){
        if( $request->ajax() ){//comes from front end via js
            $settings = array_get($request, 'data');
            //if(array_get($settings, 'type') == 'sms'){
                if(array_get($settings, 'message.list_id') == 0){
                    $list = null;
                    $include_list_tags = [];
                    $exclude_list_tags = [];
                    $final_list = array_pluck(Contact::all(), 'id');
                }
                else{
                    $list = Lists::find(array_get($settings, 'message.list_id'));
                
                    extract($this->getContactLists($list));
                    // provides $full_list, $exclude_list, and $final_list
                }
                return $this->makeSummary($request, $settings, $list, $final_list, $queue);
            //}
        }
        else if (!is_null($sms)){//comes from database
            $list = $sms->lists()->first();
            $include_list_tags = [];
            $exclude_list_tags = [];
            if(is_null($list)){//if list is null then sms was sent to everyone
                $final_list = array_pluck(Contact::all(), 'id');
            }
            else{
                extract($this->getContactLists($list));
                // provides $full_list, $exclude_list, and $final_list
            }
            return $this->makeSummary($request, null, $list, $final_list, $queue);
        }
    }

    private function makeSummary($request, $settings, $list, $final_list, $queue = false){
        //return response()->json(array_get($request, 'data.page', 0));
        $skip = 0;
        $take = 50;
        $page = (int)array_get($request, 'data.page', 0);
        if($page > 1){
            $skip = $page * $take;
        }
        $include_ids = [];
        if(is_null($settings)){//db
            $include_sms_tags = array_get($sms, 'includeTags');
        }
        else{//ajax
            $include_sms_tags = Tag::whereIn('id', array_get($settings, 'tags.include'))->get();
        }
        
        if (!is_null($include_sms_tags) && $include_sms_tags->count() > 0) {
            $include_contacts = Contact::whereHas('tags', function($query) use ($include_sms_tags) {
                $query->whereIn('id', array_pluck($include_sms_tags, 'id'));
            })->get();
            $include_ids = array_pluck($include_contacts, 'id');
        }
        $included = count($include_ids) > 0 ? array_intersect($include_ids, $final_list) : $final_list;
        
        $exclude_ids = [];
        if(is_null($settings)){//db
            $exclude_sms_tags = array_get($sms, 'excludeTags');
        }
        else{//ajax
            $exclude_sms_tags = Tag::whereIn('id', array_get($settings, 'tags.exclude'))->get();
        }
        
        if (!is_null($exclude_sms_tags) && $exclude_sms_tags->count() > 0) {
            $exclude_contacts = Contact::whereHas('tags', function($query) use ($exclude_sms_tags) {
                $query->whereIn('id', array_pluck($exclude_sms_tags, 'id'));
            })->get();
            $exclude_ids = array_pluck($exclude_contacts, 'id');
        }
        $unsubscribed_ids = array_pluck(Unsubscribe::where('list_id', array_get($list, 'id'))->get(), 'contact_id');
        $excluded = array_merge($exclude_ids, $unsubscribed_ids);
        
        $search_for = array_diff($included, $excluded);
        $send_to_all = array_get($settings, 'audience.send_all', false);
        //check who already has an email
        $number_of_days = array_get($settings, 'audience.do_not_send_within_number_of_days', 0);
        $now = Carbon::now();
        $do_not_sent_between = $now->copy()->subDays($number_of_days);

        $sent_between = [];
        if ($number_of_days > 0) {
            $sent = SMSSent::whereHas('content', function($query) use($list) {
                $query->where('list_id', array_get($list, 'id'));
            })
            ->whereBetween('sent_at', [$do_not_sent_between->startOfDay(), $now->endOfDay()])
            ->get();
            
            $sent_between = array_pluck($sent, 'to_contact_id');
        }

        $phone = SMSPhoneNumber::findOrFail(array_get($settings, 'message.sms_phone_number_id'));
        $phoneNumber = array_get($phone, 'phone_number');
        
        if ($send_to_all) {
            //$contacts = Contact::whereIn('id', $search_for)->whereNotIn('id', $sent_between)->get();
            $builder = Contact::whereIn('id', $search_for)
                    ->where('phone_numbers_only', '!=', '')
                    ->where(function ($query) use ($phoneNumber) {
                        $query->whereRaw('not find_in_set(?, unsubscribed_from_phones)', [$phoneNumber])
                                ->orWhereNull('unsubscribed_from_phones');
                    })
                    ->whereNotIn('id', $sent_between)
                    ->whereNotNull('phone_numbers_only')
                    ->hasUsPhoneNumber();

            if($queue){
                $contactsIn = $builder->get();
            }
            else{
                $contactsIn = $builder->skip($skip)->take($take)->get();
            }
            
            $inPages = Contact::whereIn('id', $search_for)
                ->where('phone_numbers_only', '!=', '')
                ->whereNotIn('id', $sent_between)
                ->whereNotNull('phone_numbers_only')
                ->hasUsPhoneNumber()
                ->count();

            $contactsOut = Contact::whereIn('id', $search_for)
                    ->where(function($query){
                        $query->where('phone_numbers_only', '')
                                ->orWhereNull('phone_numbers_only');
                    })
                    ->whereNotIn('id', $sent_between)
                    ->skip($skip)->take($take)->get();
            
            $contactsUnsubscribed = Contact::whereIn('id', $search_for)
                    ->whereRaw('find_in_set(?, unsubscribed_from_phones)', [$phoneNumber])
                    ->whereNotIn('id', $sent_between)
                    ->skip($skip)->take($take)->get();
                    
            $contactsNonUsPhoneNumber = Contact::whereIn('id', $search_for)
                    ->hasNonUsPhoneNumber()
                    ->whereNotIn('id', $sent_between)
                    ->skip($skip)->take($take)->get();
            
            $outPages = Contact::whereIn('id', $search_for)
                ->where(function($query){
                    $query->where('phone_numbers_only', '')
                            ->orWhereNull('phone_numbers_only');
                })
                ->whereNotIn('id', $sent_between)
                ->count();

            $pages = $inPages > $outPages ? ceil($inPages/$take) : ceil($outPages/$take);
        } else {
            
            $contactsIn = Contact::whereIn('id', $search_for)
                    ->where('phone_numbers_only', '!=', '')
                    ->where(function ($query) use ($phoneNumber) {
                        $query->whereRaw('not find_in_set(?, unsubscribed_from_phones)', [$phoneNumber])
                                ->orWhereNull('unsubscribed_from_phones');
                    })
                    ->whereNotIn('id', $sent_between)
                    ->whereNotNull('phone_numbers_only')
                    ->hasUsPhoneNumber()
                    ->limit(array_get($settings, 'audience.send_number_of_messages'))
                    ->get();

            $contactsOut = [];
            $contactsUnsubscribed = [];
            $contactsNonUsPhoneNumber = [];
            $number_of_messages = (int) array_get($settings, 'audience.send_number_of_messages');
            if(count($contactsIn) < $number_of_messages){
                $limit = count($contactsIn) - $number_of_messages;
                $contactsOut = Contact::whereIn('id', $search_for)
                    ->where(function($query){
                        $query->where('phone_numbers_only', '')
                                ->orWhereNull('phone_numbers_only');
                    })
                    ->whereNotIn('id', $sent_between)
                    ->limit($limit)
                    ->get();
                    
                $contactsUnsubscribed = Contact::whereIn('id', $search_for)
                    ->whereRaw('find_in_set(?, unsubscribed_from_phones)', [$phoneNumber])
                    ->whereNotIn('id', $sent_between)
                    ->limit($limit)
                    ->get();
                
                $contactsNonUsPhoneNumber = Contact::whereIn('id', $search_for)
                    ->hasNonUsPhoneNumber()
                    ->whereNotIn('id', $sent_between)
                    ->limit($limit)
                    ->get();
            }
            
            $pages = 1;
        }

        $in = [];
        $out = [];
        foreach($contactsIn as $contact){
            array_push($in, implode('', [
                array_get($contact, 'first_name'),
                ' ',
                array_get($contact, 'last_name'),
                ' (',
                array_get($contact, 'cell_phone'),
                ')'
            ]));
        }

        foreach($contactsOut as $contact){
            array_push($out, implode('', [
                array_get($contact, 'first_name'),
                ' ',
                array_get($contact, 'last_name'),
                ' (No phone number)'
            ]));
        }
        
        foreach($contactsUnsubscribed as $contact){
            array_push($out, implode('', [
                array_get($contact, 'first_name'),
                ' ',
                array_get($contact, 'last_name'),
                " (Unsubscribed from $phoneNumber)"
            ]));
        }
        
        foreach($contactsNonUsPhoneNumber as $contact){
            array_push($out, implode('', [
                array_get($contact, 'first_name'),
                ' ',
                array_get($contact, 'last_name'),
                " (Non US phone number $contact->cell_phone)"
            ]));
        }

        sort($in);
        sort($out);

        //get tags realted to actions
        $actions = [];
        foreach(array_get($settings, 'actions', []) as $action){
            if( array_get($action, 'tag', 0) > 0 ){
                $tag = Tag::find(array_get($action, 'tag'));
                array_set($action, 'tag', $tag);
            }
            array_push($actions, $action);
        }

        $data = [
            'list' => !empty($list) ? $list : ["id" => 0, "name" => "Everyone"],
            'include_lists_tags' => !empty($list) ? $list->inTags : [],
            'exclude_lists_tags' => !empty($list) ? $list->notInTags : [],
            'include_sms_tags' => $include_sms_tags,
            'exclude_sms_tags' => $exclude_sms_tags,
            'actions' => $actions,
            'in' => $in,
            'out' => $out,
            'pages' => $pages,
            'contacts' => $queue ? $contactsIn : []
        ];
        
        return $data;
    }
    
}
