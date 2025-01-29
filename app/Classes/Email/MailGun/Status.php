<?php

namespace App\Classes\Email\Mailgun;

use App\Classes\Email\Mailgun\API as MailgunAPI;
use Carbon\Carbon;
use App\Models\Contact;
use App\Models\Email;
use App\Models\EmailSent;
use App\Models\EmailTracking;
use App\Models\Lists;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use App\Models\Folder;
use App\Constants;
use App\Models\ExternalApiRequest;
use App\Models\Mailgun;


/**
 * IMPORTANT - This is now deprecated because we are now using webhooks to update email tracking
 *
 * @author josemiguel
 */
class Status {

    const NUMBER_OF_RECORDS = 100;
    const API_NAME = 'mailgun';

    public function run() {
        
        $begin = '';
        $end = '';
        
        abort(404);
        
        $tenants = EmailSent::withoutGlobalScopes()
                ->select('tenant_id')
                ->whereNotNull('tenant_id')
                ->groupBy('tenant_id')->get();

        foreach ($tenants as $tenant) {
            $credentials = Mailgun::withoutGlobalScopes()->where('tenant_id', array_get($tenant, 'tenant_id'))->first();
            if( !is_null($credentials) ){
                $mailgun = new MailgunAPI(array_get($credentials, 'domain'), array_get($credentials, 'secret'));
            }
            else{
                $mailgun = new MailgunAPI();
            }

            $page = null;
            $loop = true;

            $args = [
                'limit' => self::NUMBER_OF_RECORDS,
                'begin' => strtotime($begin),
                'end' => strtotime($end)
            ];
            
            while ($loop) {
                $status = $mailgun->status($page, $args);
                $items = array_get($status, 'items', []);
                $paging = array_get($status, 'paging', []);
                $page = array_get($paging, 'next');
                $loop = $this->check($items);
            }

            unset($mailgun);
        }
    }

    private function check($items) {
        $loop = count($items) > 0 ? true : false;
        if (!$loop) {
            return $loop;
        }
        foreach ($items as $item) {
            $timestamp = array_get($item, 'timestamp');
            $date = Carbon::createFromTimestamp($timestamp);

            $contact = Contact::withoutGlobalScopes()
                    ->join('email_sent', 'email_sent.contact_id', '=', 'contacts.id')
                    ->join('email_content', 'email_content.id', '=', 'email_sent.email_content_id')
                    ->select('contacts.id', 'contacts.tenant_id', 'email_sent.email_content_id', 'email_sent.id as email_sent_id')
                    ->where([
                        ['email_sent.swift_id', '=', array_get($item, 'message.headers.message-id')],
                        ['contacts.email_1', '=', array_get($item, 'recipient')]
                    ])
                    ->first();

            if (!is_null($contact)) {
                $email = Email::withoutGlobalScopes()->where('id', array_get($contact, 'email_content_id'))->first();
                if (!is_null($email)) {
                    $sent = EmailSent::withoutGlobalScopes()->where('id', array_get($contact, 'email_sent_id'))->first();
                    $this->track($item, $email, $sent, $contact, $date);
                }
            }
        }
        return $loop;
    }

    private function track($item, $email, $sent, $contact, $timestamp = null) {
        $instance = $email->getRelationTypeInstance()->withoutGlobalScopes()->first();

        if (!is_null($instance)) {
            if (get_class($instance) === Lists::class) {
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
                $tracking = EmailTracking::withoutGlobalScopes()->where($wheres)
                                ->orWhere($orWheres)->first();
                
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
                    array_set($tracking, 'list_id', array_get($instance, 'id'));
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

                    $statuses = [
                        'Queued',
                        'sent',
                        'accepted',
                        'delivered',
                        'opened',
                        'clicked',
                        'unsubscribed',
                    ];

                    $old_status = array_get($sent,'status');
                    $status = array_get($item, 'event');
                    $updateStatus = !in_array($status, $statuses) || array_search($old_status, $statuses) < array_search($status, $statuses);
                    
                    if (!is_null($tracking) && $updateStatus) {
                        if (array_get($tracking, 'created_at') > array_get($sent, 'updated_at')) {
                            DB::table('email_sent')->where('id', array_get($sent, 'id'))->update([
                                'status' => array_get($tracking, 'status'),
                                'updated_at' => Carbon::now(),
                                'message' => null
                            ]);
                        }
                    }
                }
            }
        }
    }
}
