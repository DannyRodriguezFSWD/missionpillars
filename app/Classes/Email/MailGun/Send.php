<?php

namespace App\Classes\Email\Mailgun;

use App\Classes\Email\Mailgun\MailgunRuntime;
use App\Constants;
use App\Mail\ContactEmail;
use App\Models\Communication;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Tag;
use App\Models\EmailLog;
use App\Models\EmailTracking;
use App\Models\User;
use App\Models\TransactionTemplate;
use App\Models\Tenant;
use App\Traits\CommunicationTrait;
use App\Traits\Emails\EmailTrait;

use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Description of Send
 *
 * @author josemiguel
 */
class Send {

    use EmailTrait;

    const NUMBER_OF_RECORDS = 200;

    public function run()
    {
        $query = $this->getQueuedEmailsQuery();

        $queue = $query->where('email_sent.sent', false)
            ->where(function ($q) {
                $q->where('email_content.time_scheduled', '<=', date('Y-m-d H:i:s'))->orWhereNull('email_content.time_scheduled');
            })
            ->orderBy('email_content.id', 'asc')
            ->limit(self::NUMBER_OF_RECORDS)
            ->get();

        foreach ($queue as $item) {
            $ccopy = array_get($item,'cc_secondary') && array_get($item,'email_2') ? array_get($item,'email_2') : null;
            $from = [
                'email' => array_get($item, 'from_email'),
                'name' => array_get($item, 'from_name')
            ];

            \App\Scopes\TenantScope::useTenantId($item->tenant_id);

            $log = EmailLog::withoutGlobalScopes()->where('email_sent_id', array_get($item, 'id'))->first();
            
            if (!empty($log)) {
                continue;
            }
            
            EmailLog::create([
                'tenant_id' => array_get($item, 'tenant_id'),
                'email_sent_id' => array_get($item, 'id')
            ]);
            
            $data = $this->prepareEmailData($item);
            array_set($data, 'contentText', $this->getTextOnlyContent($item->queued_by, $data, array_get($item, 'email_editor_type', 'tiny')));
            
            $attachments = Document::where('relation_id', array_get($item, 'eid'))->where('relation_type', Communication::class)->get();
            
            try {
                MailgunRuntime::setOutgoingDomain(array_get($item, 'tenant_id'));

                Mail::send([$this->getEmailLayout($item->queued_by, array_get($item, 'email_editor_type', 'tiny')), 'emails.send.master-layout-text'], $data, function($message) use($item, $from, $ccopy, $attachments) {
                    if(empty(array_get($from, 'email'))){
                        array_set($from, 'email', config('mail.from.address'));
                    }

                    if(empty(array_get($from, 'name'))){
                        array_set($from, 'name', config('mail.from.name'));
                    }

                    if(empty(array_get($item, 'reply_to'))){
                        array_set($item, 'reply_to', array_get($from, 'email'));
                    }

                    $message->from(config('mail.from.address'), array_get($from, 'name'))
                            ->to(array_get($item, 'to'), array_get($item, 'first_name'))
                            ->subject(array_get($item, 'subject'))
                            ->replyTo(array_get($item, 'reply_to'), array_get($item, 'from_name'));
                    
                    if ($attachments) {
                        foreach ($attachments as $attachment) {
                            $path = array_get($attachment, 'disk') === 's3' ? Storage::disk('s3')->url(array_get($attachment, 'absolute_path')) : array_get($attachment, 'absolute_path');
                            $message->attach($path, ['as' => array_get($attachment, 'name')]);
                        }
                    }
                    
                    if ($ccopy) $message->cc($ccopy, array_get($item, 'first_name'));
                    // array_get($from, 'name', config('mail.from.name'))
                    $this->sent($item, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.SENT'), array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.SENT'), $message->getSwiftMessage()->getId());

                    if (!is_null(array_get($item, 'track_and_tag_events'))) {
                        $tracks = json_decode(array_get($item, 'track_and_tag_events'), true);
                        if (array_has($tracks, 'sent')) {
                            $c = Contact::withoutGlobalScopes()->where('id', array_get($item, 'cid'))->first();
                            $tag = Tag::withoutGlobalScopes()->where('id', array_get($tracks, 'sent'))->first();
                            if(!is_null($tag)){
                                $c->tags()->sync(array_get($tag, 'id'), false);
                            }
                        }
                    }
                }); 
            } catch (\Exception $e) {
                $this->sent($item, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.SENT'), array_get(Constants::NOTIFICATIONS, 'EMAIL.MESSAGE.SENT'));
                $this->sent($item, array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.ERROR'), $e->getMessage());
                if (!is_null(array_get($item, 'track_and_tag_events'))) {
                    $tracks = json_decode(array_get($item, 'track_and_tag_events'), true);
                    if (array_has($tracks, 'error')) {
                        $c = Contact::withoutGlobalScopes()->where('id', array_get($item, 'cid'))->first();
                        $tag = Tag::withoutGlobalScopes()->where('id', array_get($tracks, 'error'));
                        if(!is_null($tag)){
                            $c->tags()->sync(array_get($tag, 'id'), false);
                        }
                    }
                }
            }
        }
    }

    private function sent($item, $status, $message, $swift = null) {
        $update = [
            'sent' => true,
            'status' => $status,
            'message' => $message,
            'updated_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'swift_id' => $swift
        ];

        $where = [
            ['contact_id', '=', array_get($item, 'cid')],
            ['email_content_id', '=', array_get($item, 'eid')]
        ];

        if ($status === array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.SENT')) {
            array_push($where, ['sent', '=', false]);
        } else if ($status === array_get(Constants::NOTIFICATIONS, 'EMAIL.STATUS.ERROR')) {
            array_push($where, ['sent', '=', true]);
            array_push($where, ['status', '=', 'sent']);
        }

        DB::table('email_sent')->where($where)->update($update);

        $tenant = \App\Models\Tenant::find(array_get($item, 'tenant_id'));
        $folder = Folder::findOrCreate('Emails', 'TAGS', $tenant, true);
        $tag = Tag::findOrCreate($status, $folder, $tenant, true);
        $c = Contact::withoutGlobalScopes()->where('id', array_get($item, 'cid'))->first();
        $c->tags()->sync(array_get($tag, 'id'), false);

        EmailTracking::insertIfStatusDoesNotExists([
            'tenant_id' => array_get($item, 'tenant_id'),
            'email_sent_id' => array_get($item, 'id'),
            'list_id' => array_get($item, 'list_id'),
            'contact_id' => array_get($item, 'cid'),
            'status' => $status,
            'reason' => $message
        ]);
    }

    private function getEmailLayout($queued_by, $editor = 'tiny')
    {
        if (in_array($queued_by,["events.purchase.tickets.checkout","events.purchase.ticket.free","events.purchase.ticket.paid","events.purchase.tickets.checkout","events.autocheckin.for.free"])) {
            return 'emails.send.events.layout';
        } else {
            if ($editor === 'topol') {
                return 'emails.send.general-topol';
            } else {
                return 'emails.send.general';
            }
        }
    }
    
    private function getTextOnlyContent($queued_by, $data, $editor = 'tiny')
    {
        array_set($data, 'textOnly', true);
        
        if (in_array($queued_by,["events.purchase.tickets.checkout","events.purchase.ticket.free","events.purchase.ticket.paid","events.purchase.tickets.checkout","events.autocheckin.for.free"])) {
            $html = view('emails.send.events.layout', $data)->render();
        }
        else {
            if ($editor === 'topol') {
                $html = view('emails.send.general-topol', $data)->render();
            } else {
                $html = view('emails.send.general', $data)->render();
            }
        }
        
        return stripAllHtmlTags($html);
    }
}
