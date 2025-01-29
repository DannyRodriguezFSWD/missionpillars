<?php

namespace App\Classes\Twilio;

use App\Classes\MissionPillarsLog;
use App\Classes\Twilio\TwilioAPI;
use App\Models\Contact;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Folder;
use App\Models\Tag;
use App\Constants;
use DB;
use App\Models\Address;
use App\Models\Country;
/**
 * Description of TwilioSender
 *
 * @author josemiguel
 */
class TwilioSender
{
    const NUMBER_OF_RECORDS = 100;
    
    private $fields = [
        'contacts.uuid as cuuid',
        'contacts.id as cid',
        'contacts.salutation',
        'contacts.preferred_name',
        'contacts.first_name',
        'contacts.last_name',
        'contacts.email_1 as to',
        'contacts.cell_phone',
        'contacts.phone_numbers_only',
        'contacts.company',
        'contacts.position',
        'sms_sent.id as sms_sent_id',
        'sms_content.id as sms_id',
        'sms_content.list_id',
        'sms_content.track_and_tag_events',
        'sms_content.content',
        'sms_content.relation_id',
        'sms_content.relation_type',
        'sms_content.created_by as user_id',
        'sms_content.queued_by',
        'sms_content.sms_phone_number_from',
        'sms_content.add_unsubscribe_message',
        'tenants.id as tenant_id',
        'tenants.organization',
        'tenants.subdomain',
        'sms_sent.*',
        'lists.permission_reminder'
    ];
    
    const STOP_TO_UNSUBSCRIBE = "\n\nSTOP to unsubscribe";
    
    private function queue() 
    {
        $queue = Contact::withoutGlobalScopes()
                ->join('sms_sent', 'contacts.id', '=', 'sms_sent.to_contact_id')
                ->join('sms_content', 'sms_content.id', '=', 'sms_sent.sms_content_id')
                ->join('tenants', 'tenants.id', '=', 'sms_content.tenant_id')
                ->leftJoin('lists', 'lists.id', '=', 'sms_content.list_id')
                ->select($this->fields)
                ->where('sms_sent.sent', false)
                ->where(function ($q) {
                    $q->where('sms_content.time_scheduled', '<=', date('Y-m-d H:i:s'))->orWhereNull('time_scheduled');
                })
                ->orderBy('sms_content.id', 'asc')
                ->limit(self::NUMBER_OF_RECORDS)
                ->get();
        return $queue;
    }

    public function run() 
    {
        $queue = $this->queue();
        
        if (!is_null($queue) && $queue->count() > 0) {
            $twilio = new TwilioAPI();
            foreach ($queue as $item) {
                $from = array_get($item, 'sms_phone_number_from');
                $to = array_get($item, 'phone_numbers_only');
                $reply = base64_encode(array_get($item, 'id'));

                $address = Address::withoutGlobalScopes()->where([
                    ['relation_id', '=', array_get($item, 'cid')],
                    ['relation_type', '=', Contact::class]
                ])->first();

                $country = Country::withoutGlobalScopes()->where('id', array_get($address, 'country_id'))->first();
                
                if (is_null($country)) {
                    $country = Country::withoutGlobalScopes()
                            ->where('iso_3166_2', array_get($address, 'country'))
                            ->orWhere('iso_3166_3', array_get($address, 'country'))
                            ->first();
                }
                
                if (!is_null($country)) {
                    $pos = strpos($to, array_get($country, 'calling_code'));
                    $to = $pos === 0 ? '+'.$to : '+'.array_get($country, 'calling_code').$to;
                }

                $message = replaceMergeCodes(array_get($item, 'content'), $item);
                
                if (array_get($item, 'add_unsubscribe_message')) {
                    $message.= self::STOP_TO_UNSUBSCRIBE;
                }
                
                try {
                    $result = $twilio->sendMessage($from, $to, $message);
                    $this->sent($item, array_get(Constants::NOTIFICATIONS, 'SMS.STATUS.SENT'), array_get(Constants::NOTIFICATIONS, 'SMS.MESSAGE.SENT'));
                } catch(\Exception $e) {
                    $this->sent($item, array_get(Constants::NOTIFICATIONS, 'SMS.STATUS.ERROR'), $e->getMessage());

                    $contact = Contact::withoutGlobalScopes()->where('id', array_get($item, 'cid'))->first();
                    $this->trackAndTagEvents($contact, $item, 'message_failed');
                }
            }
        }
    }

    private function sent($item, $status, $message){
        $update = [
            'sent' => true,
            'status' => $status,
            'message' => $message,
            'updated_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
        ];

        $where = [
            ['to_contact_id', '=', array_get($item, 'cid')],
            ['id', '=', array_get($item, 'sms_sent_id')],
            ['sent', '=', false]
        ];
        
        DB::table('sms_sent')->where($where)->update($update);

        $tenant = Tenant::find(array_get($item, 'tenant_id'));
        $folder = Folder::findOrCreate('SMS', 'TAGS', $tenant, true);
        $tag = Tag::findOrCreate($status, $folder, $tenant, true);
        $contact = Contact::withoutGlobalScopes()->where('id', array_get($item, 'cid'))->first();

        $this->trackAndTagEvents($contact, $item, 'message_sent', $tags = [array_get($tag, 'id')]);
    }

    public function trackAndTagEvents($contact, $sms_content, $event_type, $other_tags = []){
        $tags = $other_tags;
        $track_and_tag_events = json_decode(array_get($sms_content, 'track_and_tag_events', '[]'), true);
        foreach($track_and_tag_events as $event){
            if(array_get($event, 'input') == $event_type && array_get($event, 'tag') > 0){
                array_push($tags, array_get($event, 'tag'));
            }
        }
        if(count($tags) > 0){
            $contact->tags()->sync($tags, false);
        }
    }
}
