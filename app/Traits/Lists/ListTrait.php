<?php

namespace App\Traits\Lists;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\Contact;
use App\Models\EmailSent;
use Ramsey\Uuid\Uuid;
use App\Models\Unsubscribe;
use App\Classes\Email\EmailQueue;

/**
 *
 * @author josemiguel
 */
trait ListTrait {

    private function getDefaultColumnValues() {
        return [
            'created_at' => Carbon::now(),
            'created_by' => auth()->id(),
            'created_by_session_id' => Session::getId(),
            'updated_by_session_id' => Session::getId()
        ];
    }

    private function getDataInfo($email) {
        $include_list_tags = array_pluck($this->inTags, 'id');
        $exclude_list_tags = array_pluck($this->notInTags, 'id');

        $full_list = Contact::whereHas('tags', function($query) use($include_list_tags) {
                    $query->whereIn('id', $include_list_tags);
                })->get();

        $exclude_list = Contact::whereHas('tags', function($query) use($exclude_list_tags) {
                    $query->whereIn('id', $exclude_list_tags);
                })->get();

        $final_list = array_diff(array_pluck($full_list, 'id'), array_pluck($exclude_list, 'id'));

        $include_ids = [];
        $include_email_tags = array_get($email, 'includeTags');
        
        if (!is_null($include_email_tags) && $include_email_tags->count() > 0) {
            $include_contacts = Contact::whereHas('tags', function($query) use ($include_email_tags) {
                        $query->whereIn('id', array_pluck($include_email_tags, 'id'));
                    })->get();
            $include_ids = array_pluck($include_contacts, 'id');
        }
        $included = count($include_ids) > 0 ? array_intersect($include_ids, $final_list) : $final_list;

        $exclude_ids = [];
        $exclude_email_tags = array_get($email, 'excludeTags');
        if (!is_null($exclude_email_tags) && $exclude_email_tags->count() > 0) {
            $exclude_contacts = Contact::whereHas('tags', function($query) use ($exclude_email_tags) {
                        $query->whereIn('id', array_pluck($exclude_email_tags, 'id'));
                    })->get();
            $exclude_ids = array_pluck($exclude_contacts, 'id');
        }
        $unsubscribed_ids = array_pluck(Unsubscribe::where('list_id', array_get($this, 'id'))->get(), 'contact_id');
        $excluded = array_merge($exclude_ids, $unsubscribed_ids);
        
        $search_for = array_diff($included, $excluded);
        $send_to_all = array_get($email, 'send_to_all', false);
        //check who already has an email
        $number_of_days = array_get($email, 'do_not_send_within_number_of_days', 0);
        $now = Carbon::now();
        $do_not_sent_between = $now->copy()->subDays($number_of_days);
        
        $sent_between = [];
        if ($number_of_days > 0) {
            $sent = EmailSent::whereHas('content', function($query) use($email) {
                        $query->where('list_id', array_get($email, 'list_id'));
                    })
                    ->whereBetween('sent_at', [$do_not_sent_between->startOfDay(), $now->endOfDay()])
                    ->get();
            $sent_between = array_pluck($sent, 'contact_id');
            //dd($search_for, $sent_between, $sent);
        }

        if ($send_to_all) {
            $contacts = Contact::whereIn('id', $search_for)->whereNotIn('id', $sent_between)->get();
        } else {
            $contacts = Contact::whereIn('id', $search_for)
                    ->whereNotIn('id', $sent_between)
                    ->limit(array_get($email, 'send_number_of_emails'))
                    ->get();
        }
        
        $data = [
            'include_lists_tags' => $include_list_tags,
            'exclude_lists_tags' => $exclude_list_tags,
            'include_email_tags' => $include_email_tags,
            'exclude_email_tags' => $exclude_email_tags,
            'contacts' => $contacts
        ];

        return $data;
    }

    public function summary($email) {
        return $this->getDataInfo($email);
    }

    public function sendEmail($email) {
        $data = $this->getDataInfo($email);
        $contacts = array_get($data, 'contacts');

        $communicationContent = [
            'tenant_id' => array_get($email, 'tenant_id'),
            'subject' => array_get($email, 'subject'),
            'content' => array_get($email, 'content'),
            'editor_type' => array_get($email, 'email_editor_type'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $communicationContentId = DB::table('communication_contents')->insertGetId($communicationContent);
        
        foreach ($contacts as $contact) {
            $sent = null;
            if (is_null($sent) || $sent->count() <= 0) {
                EmailQueue::queue($contact, $email, $communicationContentId);
            }
        }
    }

    
}
