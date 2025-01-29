<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Models\Contact;
use App\Models\Email;
use App\Models\EmailSent;
use App\Models\EmailTracking;
use App\Models\Folder;
use App\Models\Lists;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DebugDump;

class MailgunWebhooksController extends Controller
{

    private $statuses = ['Queued', 'sent', 'rejected', 'delivered', 'failed', 'opened', 'clicked', 'unsubscribed'];

    public function __construct()
    {
        $verified = $this->verify(array_get(request(), 'signature.token'), array_get(request(), 'signature.timestamp'), array_get(request(), 'signature.signature'));
        if (!$verified) abort(403,'Unauthorized!');
    }

    public function index(Request $request)
    {
        $data = array_get($request, 'event-data');
        $this->check($data);
    }


    private function check($item)
    {
        $timestamp = array_get($item, 'timestamp');
        $date = Carbon::createFromTimestamp($timestamp);

        $contact = $this->getContact($item);

        if (!is_null($contact)) {
            $email = Email::withoutGlobalScopes()->where('id', array_get($contact, 'email_content_id'))->first();
            if (!is_null($email)) {
                $sent = EmailSent::withoutGlobalScopes()->where('id', array_get($contact, 'email_sent_id'))->first();
                $this->track($item, $email, $sent, $contact, $date);
            }
        }
    }

    private function track($item, $email, $sent, $contact, $timestamp = null)
    {
        $instance = $email->getRelationTypeInstance()->withoutGlobalScopes()->first();

        $tracking = $this->getTracking($item, $contact, $sent, $timestamp);

        if (is_null($tracking)) {
            $tracking = [
                'tenant_id' => array_get($contact, 'tenant_id'),
                'contact_id' => array_get($contact, 'id'),
                'email_sent_id' => array_get($sent, 'id'),
                'swift_id' => array_get($item, 'message.headers.message-id'),
                'created_at' => Carbon::now(),
                'status_timestamp' => $timestamp
            ];

            $status = array_get($item, 'event');
            if ($instance instanceof Lists) array_set($tracking, 'list_id', array_get($instance, 'id'));
            array_set($tracking, 'status', $status);
            array_set($tracking, 'log_level', array_get($item, 'log-level'));
            array_set($tracking, 'reason', array_get($item, 'reason'));

            $id = DB::table('email_tracking')->insertGetId($tracking);
            //here add tags
            $tenant = \App\Models\Tenant::find(array_get($contact, 'tenant_id'));
            $folder = Folder::findOrCreate('Emails', 'TAGS', $tenant, true);
            $tag = Tag::findOrCreate($status, $folder, $tenant, true);
            $c = Contact::withoutGlobalScopes()->findOrFail(array_get($contact, 'id'));
            $c->tags()->sync(array_get($tag, 'id'), false);

            $tracking = EmailTracking::withoutGlobalScopes()->where('id', $id)->first();
            if (!is_null(array_get($email, 'track_and_tag_events'))) {
                $events = json_decode(array_get($email, 'track_and_tag_events'), true);
                if (array_has($events, $status)) {
                    $tag = Tag::withoutGlobalScopes()->where('id', array_get($events, $status))->first();

                    $c->tags()->sync(array_get($tag, 'id'), false);
                }
            }

            if (!is_null($tracking) && $this->shouldUpdateStatus($sent, $item)) {
                if (array_get($tracking, 'created_at') >= array_get($sent, 'updated_at')) {
                    DB::table('email_sent')->where('id', array_get($sent, 'id'))->update([
                        'status' => array_get($tracking, 'status'),
                        'updated_at' => Carbon::now(),
                        'message' => null
                    ]);
                }
            }
        }
    }

    /**
     * @param $item
     * @return Contact|\Illuminate\Database\Eloquent\Model|null
     */
    private function getContact($item)
    {
        return Contact::withoutGlobalScopes()
            ->join('email_sent', 'email_sent.contact_id', '=', 'contacts.id')
            ->join('email_content', 'email_content.id', '=', 'email_sent.email_content_id')
            ->select('contacts.id', 'contacts.tenant_id', 'email_sent.email_content_id', 'email_sent.id as email_sent_id')
            ->where([
                ['email_sent.swift_id', '=', array_get($item, 'message.headers.message-id')],
                ['contacts.email_1', '=', array_get($item, 'recipient')]
            ])
            ->first();
    }

    /**
     * @param $item
     * @param $contact
     * @param $sent
     * @param $timestamp
     * @return EmailTracking|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    private function getTracking($item, $contact, $sent, $timestamp)
    {
        $wheres = [
            ['swift_id', '=', array_get($item, 'message.headers.message-id')],
            ['contact_id', '=', array_get($contact, 'id')],
            ['email_sent_id', '=', array_get($sent, 'id')],
            ['status', '=', array_get($item, 'event')],
            ['status_timestamp', '=', $timestamp->toDateTimeString()]
        ];
        $orWheres = [
            ['swift_id', '=', array_get($item, 'message.headers.message-id')],
            ['contact_id', '=', array_get($contact, 'id')],
            ['email_sent_id', '=', array_get($sent, 'id')],
            ['status', '=', array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.UNSUBSCRIBED')]
        ];
        return EmailTracking::withoutGlobalScopes()->where($wheres)
            ->orWhere($orWheres)->first();
    }

    /**
     * @param $sent
     * @param $item
     * @return bool
     */
    private function shouldUpdateStatus($sent, $item)
    {
        $old_status = array_get($sent, 'status');
        $status = array_get($item, 'event');
        return !in_array($status, $this->statuses) || array_search($old_status, $this->statuses) < array_search($status, $this->statuses);
    }

    private function verify($token, $timestamp, $signature)
    {
        // check if the timestamp is fresh
        if (\abs(\time() - $timestamp) > 15) {
            return false;
        }

        // returns true if signature is valid
        return \hash_equals(\hash_hmac('sha256', $timestamp . $token, env('MAILGUN_WEBHOOK_KEY')), $signature);
    }
    
    public function testWebhook()
    {
        abort(404);
        
        $sent = [
            'status' => 'sent'
        ];
        
        $item = [
            'event' => 'failed'
        ];
        
        $shouldUpdateStatus = $this->shouldUpdateStatus($sent, $item);
        
        dd($shouldUpdateStatus);        
    }
}
