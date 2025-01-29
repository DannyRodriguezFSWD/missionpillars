<?php
namespace App\Traits\Emails;

use App\Models\Communication;
use App\Models\Contact;
use App\Models\EmailSent;
use App\Models\Transaction;
use App\Models\Unsubscribe;
use App\Models\Tenant;
use App\Models\Lists;
use App\Classes\Email\EmailQueue;
use App\Traits\CommunicationTrait;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
/**
 *
 * @author josemiguel
 */
trait EmailTrait 
{
    private $fields = [
        'contacts.id',
        'contacts.uuid as cuuid',
        'contacts.id as cid',
        'contacts.salutation',
        'contacts.preferred_name',
        'contacts.first_name',
        'contacts.last_name',
        'contacts.email_1 as to',
        'contacts.email_2 as email_2',
        'contacts.company',
        'contacts.position',
        'email_content.id as eid',
        'email_content.list_id',
        'email_content.subject',
        'email_content.preview_text',
        'email_content.exclude_tags',
        'email_content.track_and_tag_events',
        'email_content.content',
        'email_content.uuid as euuid',
        'email_content.relation_id',
        'email_content.relation_type',
        'email_content.created_by as user_id',
        'email_content.queued_by',
        'email_content.from_name',
        'email_content.from_email',
        'email_content.reply_to',
        'email_content.use_date_range',
        'email_content.include_transactions',
        'email_content.transaction_start_date',
        'email_content.transaction_end_date',
        'email_content.exclude_acknowledged_transactions',
        'email_content.cc_secondary',
        'email_content.include_public_link',
        'email_content.timezone',
        'email_content.time_scheduled',
        'email_content.email_editor_type',
        'tenants.id as tenant_id',
        'tenants.organization',
        'tenants.subdomain',
        'email_sent.*',
        'email_sent.created_at AS email_queued_at',
        'lists.permission_reminder'
    ];
    
    private function getDefaultColumnValues(){
        return [
            'created_at' => Carbon::now(),
            'created_by' => auth()->id(),
            'created_by_session_id' => Session::getId(),
            'updated_by_session_id' => Session::getId()
        ];
    }


    public function sendToContact($id) {
        $columns = $this->getDefaultColumnValues();
        return $this->contacts()->sync([$id => $columns]);
    }

    public function sendEmail() {
        $data = $this->emailSummary();
        $contacts = array_get($data, 'contacts');
        
        $communicationContent = [
            'tenant_id' => array_get($this, 'tenant_id'),
            'subject' => array_get($this, 'subject'),
            'content' => array_get($this, 'content'),
            'editor_type' => array_get($this, 'email_editor_type'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $communicationContentId = DB::table('communication_contents')->insertGetId($communicationContent);
        
        foreach ($contacts as $contact) {
            $email = $this;
            EmailQueue::queue($contact, $email, $communicationContentId);
        }
        
        if ($this->include_transactions) $this->acknowledgeTransactions($contacts->pluck('id'));
    }

    /**
     * Acknowledge this communications transactions
     * @param  [type] $contacts [description]
     * @return [type]           [description]
     */
    public function acknowledgeTransactions($contacts) {
        // acknowledge transactions
        $filters = self::getTransactionFilters($this);
        $query = Transaction::acknowledged(false)->completed()->contactIdIn($contacts);
        if (array_key_exists('tax_deductible', $filters)) {
            $query->where(function ($q) use ($filters) {
                $q->taxDeductible($filters['tax_deductible'])->orWhereNotNull('parent_transaction_id');
            });
        }
        if (array_key_exists('tagged_with_ids', $filters)) {
            $filters['tagged_with_ids'];
            $query->taggedWithIds($filters['tagged_with_ids']);
        }
        $query->update(['acknowledged'=>true,'acknowledged_at'=>gmdate('Y-m-d G:i:s')]);
    }

    /**
     * @param  [Communication|Contact] $object [description]
     */
    public static function getTransactionFilters($object) {
        $filters = [];
        $filters['tax_deductible'] = true;
        $filters['completed'] = true;
        if ($object->exclude_acknowledged_transactions) {
            $filters['acknowledged'] = false;
        }
        if ($object->use_date_range) {
            $filters['between'] = [
                'start' => new Carbon($object->transaction_start_date),
                'end' => new Carbon($object->transaction_end_date),
            ];
        }
        if ($object->transaction_tags_collection) { // HACK Send::queue loads a collection of Contact with property manually set
            if ($object->transaction_tags_collection->count()) {
                $filters['tagged_with_ids'] = $object->transaction_tags_collection->pluck('id')->toArray();
            }
        }
        elseif ($object->transactionTags()->count()) {
            $filters['tagged_with_ids'] = $object->transactionTags()->pluck('tag_id')->toArray();
        }
        
        if ($object->excluded_transaction_tags_collection) { // HACK Send::queue loads a collection of Contact with property manually set
            if ($object->excluded_transaction_tags_collection->count()) {
                $filters['not_tagged_with_ids'] = $object->excluded_transaction_tags_collection->pluck('id')->toArray();
            }
        }
        elseif ($object->excludedTransactionTags()->count()) {
            $filters['not_tagged_with_ids'] = $object->excludedTransactionTags()->pluck('tag_id')->toArray();
        }
        
        return $filters;
    }
    
    public function summary() { return $this->emailSummary(); }
    public function emailSummary() {
        $datainfo = $this->getDataInfo();
        extract($datainfo);
        
        $contacts = $contacts->get();
        $contacts_not_included = $contacts_not_included->get();
        
        return compact(
            'include_lists_tags',
            'exclude_lists_tags',
            'include_email_tags',
            'exclude_email_tags',
            'contacts',
            'contacts_not_included'
            );
    }

    /**
     * Provides [$datatable,$include_list_tags,$exclude_list_tags]
     */
    protected function getListInfo() {
        // Datatable contacts
        $datatable = $this->list()->savedSearch()->count() 
        ? \App\DataTables\ContactDataTable::createFromState($this->list->datatableState) : null;
        
        $include_list_tags = $datatable || is_null(array_get($this, 'list_id')) ? [] : array_pluck($this->lists->inTags, 'id');
        $exclude_list_tags = $datatable || is_null(array_get($this, 'list_id')) ? [] : array_pluck($this->lists->notInTags, 'id');
        
        return compact('datatable','include_list_tags','exclude_list_tags');
    }
    
    protected function getSavedSearchIds($datatable, &$yes_included, &$not_included) {
        // If saved search, get contacts from Datatable
        $saved_search_ids = collect([]);
        if ($datatable) {
            $saved_search_ids = collect($datatable->getContactIdArray());
            $yes_included->whereIn('id', $saved_search_ids);
            $not_included->whereNotIn('id', $saved_search_ids);
        }
        
        return $saved_search_ids;
    }
    private function getDataInfo() {
        extract($this->getListInfo());

        $is_saved_search = !is_null($datatable);
        $include_email_tags = array_pluck($this->includeTags, 'id');
        $exclude_email_tags = array_pluck($this->excludeTags, 'id');
        
        $excluded_tags = array_merge($exclude_list_tags,$exclude_email_tags);
        
        $unsubscribed_ids = array_pluck(Unsubscribe::where('list_id', array_get($this, 'list_id'))->get(), 'contact_id');
        
        // must include email
        $yes_included = Contact::whereNotNull('email_1')->where('email_1', '!=', '');
        $not_included = Contact::whereNull('email_1')->orWhere('email_1','');
        
        $saved_search_ids = $this->getSavedSearchIds($datatable,$yes_included,$not_included);
        
        // Apply list tags, then email tags 
        foreach ([$include_list_tags,$include_email_tags] as $included_tags) {
            if(count($included_tags) > 0){
                $yes_included->whereHas('tags', function($query) use($included_tags) {
                    $query->whereIn('id', $included_tags);
                });
            }
        }
        
        if(count($excluded_tags) > 0){
            $not_included->orWhereHas('tags', function($query) use($excluded_tags) {
                $query->whereIn('id', $excluded_tags);
            });
        }
        
        $yes_included = $yes_included->pluck('id')->toArray();
        $not_included = $not_included->pluck('id')->toArray();

        $previously_emailed = collect([]);
        if(array_get($this, 'do_not_send_to_previous_receivers') == 1){
            $previously_emailed = $this->sent()->pluck('contact_id');
            $not_included = array_merge($not_included, $previously_emailed->toArray());
        }
        
        $previously_printed = collect([]);
        if(array_get($this, 'email_exclude_printed') == 1){
            $communication = $this;
            if (get_class($this) != Communication::class) $communication = Communication::find($communication->id);
            $previously_printed = $communication->printRecipients()->pluck('contact_id');
            $not_included = array_merge($not_included, $previously_printed->toArray());
        }
        
        $full_list = array_diff($yes_included, $not_included, $unsubscribed_ids);
        
        $search_for = $full_list;
        $send_to_all = array_get($this, 'send_to_all', false);
        //check who already has an email
        $number_of_days = array_get($this, 'do_not_send_within_number_of_days', 0);
        $now = Carbon::now();
        $do_not_sent_between = $now->copy()->subDays($number_of_days);
        
        $sent_between = collect([]);
        if ($number_of_days > 0) {
            // contacts that have been sent an email for this list 'recently'
            $email = $this;
            $sent_between = EmailSent::whereNotNull('contact_id')
            ->whereBetween('sent_at', [$do_not_sent_between->startOfDay(), $now->endOfDay()])
            ->whereHas('content', function($query) use($email) {
                $query->where('list_id', $email->list_id);
            })
            ->groupBy('contact_id')->pluck('contact_id');
        }

        $contacts = Contact::whereIn('id', $search_for)
            ->whereNotIn('id', $sent_between)->with('tags')
            ->orderByRaw("case when type = 'person' then first_name else company end")
            ->orderBy('last_name');
        if (!$send_to_all) {
            $contacts->limit(array_get($this, 'send_number_of_emails'));
        }
        
        $contacts_not_included = Contact::whereIn('id', $not_included)
        ->orWhereNotIn('id', $contacts->pluck('id')->toArray())
        ->with('tags')
        ->orderByRaw("case when type = 'person' then first_name else company end")
        ->orderBy('last_name');
        
        return compact('contacts','contacts_not_included', 'unsubscribed_ids','include_list_tags','exclude_list_tags', 'include_email_tags','exclude_email_tags','sent_between','previously_emailed','previously_printed','saved_search_ids','is_saved_search');
    }
    
    public function getQueuedEmailsQuery() 
    {
        $query = Contact::withoutGlobalScopes()
                ->join('email_sent', 'contacts.id', '=', 'email_sent.contact_id')
                ->join('email_content', 'email_content.id', '=', 'email_sent.email_content_id')
                ->join('tenants', 'tenants.id', '=', 'email_content.tenant_id')
                ->leftJoin('lists', 'lists.id', '=', 'email_content.list_id')
                ->select($this->fields);
        
        return $query;
    }
    
    public function prepareEmailData($item, $mode = 'send')
    {
        if ($mode === 'send') {
            $link = sprintf(env('APP_DOMAIN'), array_get($item, 'subdomain'));
            $content = replaceMergeCodes($item->content, $item, true);
            if ($item->include_transactions) {
                $contact = $item;
                $item->transaction_tags_collection = Communication::find($item->eid)->transactionTags;
                $item->excluded_transaction_tags_collection = Communication::find($item->eid)->excludedTransactionTags;
                $filters = CommunicationTrait::getTransactionFilters($item);
                appendTransactionsToContact($contact, $item->transaction_start_date, $item->transaction_end_date, $filters, $item->timezone);
                $content = replaceTransactionCodes($content, $contact->donations, array_get($contact, 'lastTransaction'),
                $item->transaction_start_date,$item->transaction_end_date,
                true);
                $content = replaceItemListCode($content, $item, false);
                $content = replaceListOfDonationsCode($content, $item, false);
            }
            
            $list = Lists::withoutGlobalScopes()->where('id', array_get($item, 'list_id'))->first();
            $unsubscribe = $link . implode('/', ['unsubscribe', array_get($item, 'uuid')]);
            $reminder = array_get($item, 'permission_reminder');
            $cancelPledgeLink = $this->getCancelPledgeLink($item, $link);     
            $tenant = Tenant::find(array_get($item, 'tenant_id'));
            $publicLink = $link . implode('/', ['emails', array_get($item, 'uuid'), 'web']);
            $shareLink = $link . implode('/', ['communications', array_get($item, 'euuid'), 'public']);
        } elseif ($mode === 'publicView') {
            $link = sprintf(env('APP_DOMAIN'), array_get($item, 'tenant.subdomain'));
            $content = removeMergeCodes($item->content);
            $list = null;
            $unsubscribe = null;
            $reminder = null;
            $cancelPledgeLink = null;
            $tenant = $item->tenant;
            $publicLink = array_get($item, 'public_link');
            $shareLink = $publicLink;
        }

        $is_public_view = $mode === 'publicView';
        
        $data = [
            'item' => $item,
            'link' => $link,
            'unsubscribe' => $unsubscribe,
            'list' => $list,
            'reminder' => $reminder,
            'cancelPledgeLink' => $cancelPledgeLink,
            'tenant' => $tenant,
            'includePublicLink' => $is_public_view ? false : array_get($item, 'include_public_link', 0),
            'publicLink' => $publicLink,
            'shareLink' => $shareLink,
            'show_unsubscribe' => !$is_public_view && $item->relation_type == Lists::class,
            'preview_text' => array_get($item, 'preview_text')
        ];
        
        $data['content'] = array_get($item, 'email_editor_type', 'tiny') === 'topol' ? $this->prepareEmailDataTopol($content, $data) : $content;
        
        return $data;
    }
    
    private function getCancelPledgeLink($item, $link) 
    {
        $cancelPledge = null;
        
        if (array_get($item, 'relation_type') === TransactionTemplate::class && strtolower(array_get($item, 'queued_by', '')) === 'pledges.reminder') {
            $template = TransactionTemplate::withoutGlobalScopes()->where('id', array_get($item, 'relation_id'))->first();
            if(!is_null($template)){
                $cancelPledge = $link . implode('/', ['pledges', array_get($template, 'id'), 'cancel']);
            }
        }
        
        return $cancelPledge;
    }
    
    private function prepareEmailDataTopol($content, $data)
    {
        $content = $this->addPreviewTextToTopolContent($content, $data);
        
        return $this->addFooterToTopolContent($content, $data);
    }
    
    private function addPreviewTextToTopolContent($content, $data)
    {
        $preview_text = array_get($data, 'preview_text');
        
        if ($preview_text) {
            $previewTextDiv = view('emails.send.includes.preview-text', compact('preview_text'))->render();
            $bodyPosition = strpos($content, '<body');
            $divPosition = strpos($content, '<div', $bodyPosition);
            
            // add preview text at the top of the body
            $content = substr_replace($content, ' '.$previewTextDiv.' ', $divPosition, 0);
        }
        
        return $content;
    }
    
    private function addFooterToTopolContent($content, $data)
    {
        $styles = view('emails.send.includes.style-topol')->render();
        
        $headPosition = strpos($content, '</head>');
        
        // add styles at the bottom of the header
        $content = substr_replace($content, ' '.$styles.' ', $headPosition, 0);
        
        $footer = view('emails.send.includes.footer-topol')->with($data)->render();
        
        $tablePosition = strripos($content, '</tbody>');
        
        // add footer at the bottom of the ocntent
        $content = substr_replace($content, ' '.$footer.' ', $tablePosition, 0);
        
        return $content;
    }
}
