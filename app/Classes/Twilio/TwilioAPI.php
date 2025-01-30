<?php

namespace App\Classes\Twilio;

use App\Classes\Email\EmailQueue;
use App\Classes\Email\Mailgun\MailgunRuntime;
use App\Classes\MissionPillarsLog;
use App\Classes\MpWrapper\TwilioRequestClient;
use App\Models\Contact;
use App\Models\SMSContent;
use App\Models\SMSPhoneNumber;
use App\Models\SMSSent;
use App\Models\SMSTracking;
use App\Models\Tenant;
use App\Traits\Users\ContactTrait;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Ramsey\Uuid\Uuid;
/**
 * Description of Twilio
 *
 * @author josemiguel
 */
class TwilioAPI{
    use ContactTrait;
    
 
    const REPLACE_PREFIX = '+1';

    private $unsubscribeWords = [
        'stop', 'stopall', 'unsubscribe', 'cancel', 'end', 'quit'
    ];
    
    private $resubscribeWords = [
        'start', 'yes', 'unstop'
    ];
    
    protected $client;
    //https://www.twilio.com/docs/iam/test-credentials#test-incoming-phone-numbers-parameters-AreaCode
    /*
    private $test_numbers = [
        ['friendlyName' => "(500) 5550000",'phoneNumber' => "+15005550000", 'location' => "Atlanta, GA US", 'region' => "CA", 'isoCountry' => "US", 'capabilities' => ['voice' => true, 'SMS' => true, 'MMS' => true, 'fax' => false], 'description' => 'Will show: This phone number is unavailable error'],
        ['friendlyName' => "(150) 05550001",'phoneNumber' => "+15005550001", 'location' => "Atlanta, GA US", 'region' => "CA", 'isoCountry' => "US", 'capabilities' => ['voice' => true, 'SMS' => true, 'MMS' => true, 'fax' => false], 'description' => 'Will show: This phone number is invalid error'],
        ['friendlyName' => "(150) 05550006",'phoneNumber' => "+15005550006", 'location' => "Atlanta, GA US", 'region' => "CA", 'isoCountry' => "US", 'capabilities' => ['voice' => true, 'SMS' => true, 'MMS' => true, 'fax' => false], 'description' => 'This phone number is valid and available no errors will shown and will send fake SMS'],
    ];
    */

    public function __construct($sid = null, $token = null) {
        $_sid = is_null($sid) ? env('TWILIO_SID') : $sid;
        $_token = is_null($token) ? env('TWILIO_AUTH_TOKEN') : $token;

        $this->client = new Client($_sid, $_token,null,null, new TwilioRequestClient());
    }
    
    public function buyPhoneNumber($phoneNumber){
        try{
            $incoming_phone_number = $this->client->incomingPhoneNumbers->create(
                [
                    "phoneNumber" => $phoneNumber,
                    "SmsUrl" => env("TWILIO_REPLY_CALLBACK")
                ]
            );
            MissionPillarsLog::log([
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'request' => "{'phoneNumber' : '$phoneNumber'}",
                'response' => json_encode($this->arrayTransform($incoming_phone_number))
            ]);
            return [
                'status' => 200,
                'response' => $incoming_phone_number
            ];
        }
        catch(\Twilio\Exceptions\TwilioException $ex){
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getStatusCode(),
                'message' => $ex->getMessage(),
                'request' => "{'phoneNumber' : '$phoneNumber'}",
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return [
                'status' => $ex->getStatusCode(),
                'message' => $ex->getMessage()
            ];
        }
    }

    public function getAvailablePhoneNumbers($code){
        try{
            if(env('APP_ENVIROMENT') != 'production'){//search is not availabe with test credentials, so lets just fake the response
                $encoded = json_encode([
                    [
                        'friendlyName' => env('TWILIO_DEMO_FRIENDLY_PHONE_NUMBER'),
                        'phoneNumber' => env('TWILIO_DEMO_PHONE_NUMBER'),
                        'location' => "Atlanta, GA US",
                        'region' => "CA",
                        'isoCountry' => "US",
                        'capabilities' => [
                            'voice' => true,
                            'SMS' => true,
                            'MMS' => true,
                            'fax' => false
                        ],
                        'description' => 'Phone number to send real sms on demo'
                    ]
                ]);
                $phoneNumbers = json_decode($encoded);
            }
            else{
                $request = ['areaCode' => $code, 'Capabilities' => ['sms' => true]];
                $response = $this->client->availablePhoneNumbers('US')->local->read($request);
                $phoneNumbers = [];

                foreach($response as $item){
                    $phone = [
                        'friendlyName' => $item->friendlyName,
                        'phoneNumber' => $item->phoneNumber,
                        'location' => $item->locality,
                        'region' => $item->region,
                        'isoCountry' => $item->isoCountry,
                        'capabilities' => $item->capabilities
                    ];
                    array_push($phoneNumbers, $phone);
                }

                $log = [
                    'event' => 'twilio',
                    'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                    'request' => json_encode($request),
                    'response' => json_encode($phoneNumbers)
                ];
                MissionPillarsLog::log($log);
            }
            return $phoneNumbers;
        }
        catch(\Twilio\Exceptions\TwilioException $ex){
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getStatusCode(),
                'message' => $ex->getMessage(),
                'request' => "{'areaCode' : '$code'}",
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return [
                'status' => $ex->getStatusCode(),
                'message' => $ex->getMessage()
            ];
        }
    }

    public function sendMessage($from, $to, $sms){
        try{
            $message = $this->client->messages->create(
                $to,
                array(
                    "body" => $sms,
                    "from" => $from,
                    "statusCallback" => env('TWILIO_STATUS_CALLBACK')
                )
            );
            //by default tuilio does not return a response if the sms was sent successfully
            //so we just simulate one
            $result = ['status' => 200, 'message' => 'OK'];
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'request' => json_encode(['from' => $from, 'to' => $to, 'message' => $sms]),
                'response' => json_encode($result)
            ];
            MissionPillarsLog::log($log);
            return $result;
        }
        catch(\Twilio\Exceptions\TwilioException $ex){
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getStatusCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode(['from' => $from, 'to' => $to, 'message' => $sms]),
                'response' => json_encode([
                    'status' => $ex->getStatusCode(),
                    'message' => $ex->getMessage()
                ])
            ];
            MissionPillarsLog::log($log);
            throw $ex;
        }
    }

    public function sendTestMessage(SMSPhoneNumber $from, $to, $message)
    {
        try {
            $message = $this->client->messages->create($to, [
                "body" => $message,
                "from" => array_get($from, 'phone_number'),
                "statusCallback" => env('TWILIO_STATUS_CALLBACK')
            ]);

            return $message;
        } catch(\Twilio\Exceptions\TwilioException $ex) {
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getStatusCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode(['to' => $to, 'message' => $message]),
                'response' => json_encode($ex)
            ];
            MissionPillarsLog::log($log);
            return [
                'status' => $ex->getStatusCode(),
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * transforms twilio format to php array
     */
    public function arrayTransform($item){
        
        return [
            'accountSid' => $item->accountSid,
            'addressSid' => $item->addressSid,
            'addressRequirements' => $item->addressRequirements,
            'apiVersion' => $item->apiVersion,
            'beta' => $item->beta,
            'capabilities' => $item->capabilities,
            'dateCreated' => $item->dateCreated,
            'dateUpdated' => $item->dateUpdated,
            'identitySid' => $item->identitySid,
            'phoneNumber' => $item->phoneNumber,
            'origin' => $item->origin,
            'sid' => $item->sid,
            'smsApplicationSid' => $item->smsApplicationSid,
            'smsFallbackMethod' => $item->smsFallbackMethod,
            'smsFallbackUrl' => $item->smsFallbackUrl,
            'smsMethod' => $item->smsMethod,
            'smsUrl' => $item->smsUrl,
            'statusCallback' => $item->statusCallback,
            'statusCallbackMethod' => $item->statusCallbackMethod,
            'uri' => $item->uri,
            'voiceApplicationSid' => $item->voiceApplicationSid,
            'voiceCallerIdLookup' => $item->voiceCallerIdLookup,
            'voiceFallbackMethod' => $item->voiceFallbackMethod,
            'voiceFallbackUrl' => $item->voiceFallbackUrl,
            'voiceMethod' => $item->voiceMethod,
            'voiceUrl' => $item->voiceUrl,
            'emergencyStatus' => $item->emergencyStatus,
            'emergencyAddressSid' => $item->emergencyAddressSid,
        ];
    }

    public function statusCallback($request){
        $from = array_get($request, 'From');
        $to = array_get($request, 'To');
        $phoneNumbersOnly = preg_replace('/[^0-9]/','',$to);

        $phone = SMSPhoneNumber::withoutGlobalScopes()->where('phone_number', $from)->first();
        $contact = Contact::withoutGlobalScopes()
                ->where([
                    ['tenant_id', '=', array_get($phone, 'tenant_id')],
                    ['phone_numbers_only', '=', $phoneNumbersOnly]
                ])->first();
                    
        $sms = SMSContent::withoutGlobalScopes()
                ->where([
                    ['tenant_id', '=', array_get($phone, 'tenant_id')],
                    ['sms_phone_number_from', '=', $from]
                ])->orderBy('id', 'desc')->first();
    
        $sent = SMSSent::withoutGlobalScopes()
            ->where([
                ['tenant_id', '=', array_get($contact, 'tenant_id')],
                ['to_contact_id', '=', array_get($contact, 'id')],
                ['sms_content_id', '=', array_get($sms, 'id')]
            ])->first();
                
        if(!empty($sent) && !empty($contact) && !empty($sms)){
            $track = new SMSTracking();
            array_set($track, 'tenant_id', array_get($sent, 'tenant_id'));
            array_set($track, 'sms_sent_id', array_get($sent, 'id'));
            array_set($track, 'list_id', array_get($sms, 'list_id'));
            array_set($track, 'contact_id', array_get($contact, 'id'));
            array_set($track, 'sms_sid', array_get($request, 'SmsSid'));
            array_set($track, 'status', array_get($request, 'SmsStatus'));
            array_set($track, 'message', array_get($request, 'MessageStatus'));
            array_set($track, 'created_at', Carbon::now());
            array_set($track, 'updated_at', Carbon::now());
            
            $track_id = DB::table('sms_tracking')->insertGetId($track->toArray());

            if(!empty($track_id)){
                $event = '';
                switch(array_get($request, 'SmsStatus')){
                    case 'sent':
                        $event = 'message_sent';
                        break;
                    case 'delivered':
                        $event = 'message_delivered';
                        break;
                    case 'failed':
                        $event = 'message_failed';
                        break;
                    case 'undelivered':
                        $event = 'message_undelivered';
                        break;
                    default:
                        break;
                }

                if( !empty($event) ){
                    $sender = new TwilioSender();
                    $sender->trackAndTagEvents($contact, $sms, $event);
                }

                DB::table('sms_sent')->where('id', array_get($sent, 'id'))->update([
                    'status' => array_get($request, 'SmsStatus'),
                    'message' => array_get($request, 'MessageStatus')
                ]);
            }
        }
    }

    public function replyReceivedCallback($request)
    {
        try {
            MissionPillarsLog::log([
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'response' => json_encode($request->all())
            ]);

            $from = str_replace(self::REPLACE_PREFIX, '', array_get($request, 'From'));

            if(env('APP_ENVIROMENT') != 'production'){
                $from = str_replace('+52', '', array_get($request, 'From'));
            }
            
            $to = array_get($request, 'To');
            $phoneNumbersOnly = preg_replace('/[^0-9]/','',$from);
            $phoneNumbersOnly = substr($phoneNumbersOnly, -10);
            
            $phone = SMSPhoneNumber::withoutGlobalScopes()
                    ->where('phone_number', $to)
                    ->whereNull('deleted_at')
                    ->first();
            
            $contact = Contact::withoutGlobalScopes()
                    ->where([
                        ['tenant_id', '=', array_get($phone, 'tenant_id')],
                        ['phone_numbers_only', '=', $phoneNumbersOnly]
                    ])->whereNull('deleted_at')->first();
            
            $users = $phone->contacts_to_notify;

            if (is_null($contact)) {
                $contact = $this->createContact([
                    'uuid' => Uuid::uuid4(),
                    'tenant_id' => array_get($phone, 'tenant_id'),
                    'first_name' => 'Unknown contact',
                    'cell_phone' => $from,
                    'phone_numbers_only' => $phoneNumbersOnly,
                ]);
            }

            if(!empty($contact) && !empty($phone) && !empty($users)){
                $this->replyReceived($contact, $users, $phone, array_get($request, 'Body'), $from, $to);
            }
        } catch(\Exception $e){
            MissionPillarsLog::log([
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'response' => json_encode($request->all())
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            MissionPillarsLog::log([
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'response' => json_encode($request->all())
            ]);
        }
    }

    public function replyReceived($contact, $users, $phone, $content, $from, $to)
    {
        $queued_by = implode('.', [TwilioAPI::class, __FUNCTION__]);
        $receivedContentId = DB::table('sms_content')->insertGetId([
            'tenant_id' => array_get($phone, 'tenant_id'),
            'sms_phone_number_from' => $from,
            'sms_phone_number_to' => $to,
            'content' => $content,
            'queued_by' => $queued_by,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $uuid = Uuid::uuid4();
        
        foreach ($users as $user) {
            DB::table('sms_sent')->insertGetId([
                'tenant_id' => array_get($phone, 'tenant_id'),
                'to_contact_id' => array_get($user, 'id'),
                'from_contact_id' => array_get($contact, 'id'),
                'sms_content_id' => $receivedContentId,
                'sent' => true,
                'read' => false,
                'status' => 'received',
                'message' => 'Message received',
                'sent_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'uuid' => $uuid
            ]);
        }
        
        if (in_array(trim(strtolower($content)), $this->unsubscribeWords)) {
            return $this->unsubscribe($contact, $to);
        } elseif (in_array(trim(strtolower($content)), $this->resubscribeWords)) {
            return $this->resubscribe($contact, $to);
        }
        
        $emails = array_get($phone, 'emails_to_notify');
        $numbers = array_get($phone, 'phones_to_notify');
        
        $redirect = sprintf(env('APP_DOMAIN'), array_get($users[0], 'tenant.subdomain')).'crm/contacts/'.array_get($contact, 'id').'/sms';
        
        if (!empty($emails)) {
            foreach ($emails as $email) {
                $subject = array_get($contact, 'full_name').' sent you an sms '.Uuid::uuid4();

                $emailContent = '<p>You have received a sms from '.array_get($contact, 'first_name').' '.array_get($contact, 'last_name').'</p>';
                $emailContent .= '<p>'.$content.'</p>';
                $emailContent .= '<p><a href="'. $redirect .'">Click here to reply</a></p>';
                
                Mail::send('emails.send.notification', [
                    'organization' => array_get($phone, 'tenant.organization'),
                    'content' => $emailContent,
                ], function($message) use($email, $subject) {
                    $message->from(config('mail.from.address'), config('mail.from.name'))
                                ->to($email, null)
                                ->subject($subject);
                });
            }
        }
        
        if (!empty($numbers)) {
            foreach ($numbers as $number) {
                $smsContent = "You have received a sms from ".array_get($contact, 'first_name')." ".array_get($contact, 'last_name');
                $smsContent .= "\n";//line break
                $smsContent .= "follow this link to reply:";
                $smsContent .= "\n";//line break
                $smsContent .= $redirect;
                
                $this->sendMessage(array_get($phone, 'phone_number'), $number, $smsContent);
            }
        }
    }

    /**
     * @link https://www.twilio.com/docs/phone-numbers/api/incoming-phone-numbers
     */
    public function releasePhoneNumber($sid){
        try{
            $response = $this->client->incomingPhoneNumbers($sid)->delete();
            MissionPillarsLog::log([
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'response' => json_encode($response)
            ]);
            return [
                'status' => 200,
                'response' => $response
            ];
        }
        catch(\Twilio\Exceptions\TwilioException $ex){
            $log = [
                'event' => 'twilio',
                'caller_function' => implode('.', [get_class($this), __FUNCTION__]),
                'code' => $ex->getStatusCode(),
                'message' => $ex->getMessage(),
                'request' => json_encode($sid)
            ];
            MissionPillarsLog::log($log);
            return [
                'status' => $ex->getStatusCode(),
                'message' => $ex->getMessage()
            ];
        }
        
    }

    public function unsubscribe(Contact $contact, $phone)
    {
        $unsubscribedPhones = array_get($contact, 'unsubscribed_from_phones');
        
        if (empty($unsubscribedPhones)) {
            $contact->unsubscribed_from_phones = $phone;
            $contact->save();
        } else {
            $ex = explode(',', $unsubscribedPhones);
            
            if (!in_array($phone, $ex)) {
                $ex[] = $phone;
                $contact->unsubscribed_from_phones = implode(',', $ex);
                $contact->save();
            }
        }
    }
    
    public function resubscribe(Contact $contact, $phone)
    {
        $unsubscribedPhones = array_get($contact, 'unsubscribed_from_phones');
        
        $ex = explode(',', $unsubscribedPhones);

        if (in_array($phone, $ex)) {
            for ($i=0; $i<count($ex); $i++) {
                if ($ex[$i] == $phone) {
                    unset($ex[$i]);
                    break;
                }
            }
            
            $contact->unsubscribed_from_phones = implode(',', $ex);
            $contact->save();
        }
    }
}
