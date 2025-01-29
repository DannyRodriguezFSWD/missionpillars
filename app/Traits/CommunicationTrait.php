<?php
namespace App\Traits;

use Carbon\Carbon;
use App\Models\Contact;
use App\Models\PrintedCommunication;
use App\Models\Tag;
use App\Models\Unsubscribe;
use Illuminate\Support\Facades\DB;


trait CommunicationTrait {
    use Emails\EmailTrait;
    
    /**
     * Creates a summary of email communication. Used for confirming the 'email job' and determining the contacts to be emailed
     * @return [array] Array with keys include_lists_tags, exclude_list_tags, include_email_tags, exclude_email_tags, contact, contacts_not_included
     */
    public function emailSummary($paginate = false)
    {
        extract( $this->getDataInfo() );
        // additional processing
        $this->processTransactionFilters($contacts);
        $contacts = $contacts->whereNull('unsubscribed');
        $contacts_not_included->orWhereNotIn('id',$contacts->pluck('id')->toArray());
        $this->processTransactionFilters($contacts_not_included);
        $this->addTransactionCounts($contacts_not_included);
        $totalIncluded = $contacts->count();
        $totalNotIncluded = $contacts_not_included->count();
        
        $returnnotincludedpage = request()->has('page') && request()->input('page') > 1;
        $contacts = $paginate ? $contacts->simplePaginate(50) : $contacts->get();
        $contacts_not_included = $contacts_not_included->simplePaginate(50);
        $contacts_not_included->each(function($c) use ($unsubscribed_ids, $include_list_tags, $exclude_list_tags, $include_email_tags, $exclude_email_tags, $sent_between, $previously_emailed, $previously_printed, $saved_search_ids, $is_saved_search){
            $c->reasons_not_included = [
                'unsubscribed_from_all' => $c->unsubscribed,
                'unsubscribed' => in_array($c->id, $unsubscribed_ids),
                'list_not_included' => !empty($include_list_tags) && $c->tags->whereIn('id', $include_list_tags)->count() == 0,
                'email_not_included' => !empty($include_email_tags) && $c->tags->whereIn('id', $include_email_tags)->count() == 0,
                'list_excluded' => !empty($exclude_list_tags) && $c->tags->whereIn('id', $exclude_list_tags)->count() > 0,
                'email_excluded' => !empty($exclude_email_tags) && $c->tags->whereIn('id', $exclude_email_tags)->count() > 0,
                'recently_emailed' => $sent_between->contains($c->id),
                'sent_this_email' => $previously_emailed->contains($c->id),
                'sent_this_print' => $previously_printed->contains($c->id),
                'saved_search_excluded' => $is_saved_search && !$saved_search_ids->contains($c->id),
            ];
        });
        
        return compact(
            // are the next 4 needed for Communication (carryover from EmailTrait implmentation)
            'include_lists_tags',
            'exclude_lists_tags',
            'include_email_tags',
            'exclude_email_tags',
            'contacts',
            'contacts_not_included',
            'totalIncluded',
            'totalNotIncluded'
        );
    }
    
    /**
     * Creates a summary of print communication. Used for confirming the 'print job' and determining the contacts to be printed
     * @return [array] Array with keys contact, contacts_not_included
     */
    public function printSummary($paginate = false)
    {
        extract($this->getListInfo()); // provides $datatable,$include_list_tags,$exclude_list_tags

        $is_saved_search = !is_null($datatable);
        $include_print_tags = $this->printIncludeTags()->pluck('tag_id')->toArray();
        $exclude_print_tags = $this->printExcludeTags()->pluck('tag_id')->toArray();
        
        $excluded_tags = array_merge($exclude_list_tags,$exclude_print_tags);
        
        $unsubscribed_ids = Unsubscribe::where('list_id', array_get($this, 'list_id'))->pluck('contact_id');
        
        $yes_included = Contact::query();
        $not_included = Contact::whereNull('id'); // Starting false assumption
        
        // modifies $yes_included and $not_included
        $saved_search_ids = $this->getSavedSearchIds($datatable,$yes_included,$not_included);
        
        if ($this->print_only_paper_statement_contacts) {
            $yes_included->where('send_paper_contribution_statement',1);
            $not_included->orWhere('send_paper_contribution_statement','!=',1);
        }
        
        // Apply list tags, then print tags 
        foreach ([$include_list_tags,$include_print_tags] as $included_tags) {
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
        
        $yes_included = $yes_included->pluck('id');
        $not_included = $not_included->pluck('id');

        $previously_printed = collect();
        if ($this->print_exclude_printed){
            $previously_printed = $this->printRecipients()->pluck('contact_id');
            $not_included = $not_included->merge($previously_printed);
        }
        
        $previously_emailed = collect();
        if ($this->print_exclude_emailed){
            $previously_emailed = $this->sent()->pluck('contact_id');
            $not_included = $not_included->merge($previously_emailed);
        }
        
        $yes_included = $yes_included->diff($not_included)->diff($unsubscribed_ids);
        
        //check who already has had a print communication
        $number_of_days = $this->print_exclude_recent_ndays ?: 0;
        $now = Carbon::now();
        $do_not_sent_between = $now->copy()->subDays($number_of_days);
        
        $sent_between = collect([]);
        if ($number_of_days > 0) {
            // ALL contacts that have had ANY print communications 'recently'
            $between = ['start'=>$do_not_sent_between, 'end'=>$now];
            $sent_between = PrintedCommunication::between($between)->distinct()->pluck('contact_id');
        }

        $contacts = Contact::whereIn('id', $yes_included)
            ->whereNotIn('id', $sent_between)->with('tags')
            ->orderByRaw("case when type = 'person' then last_name else company end")
            ->orderBy('first_name');
        if ($this->print_limit_contacts) {
            $contacts->limit($this->print_max_contacts);
        }
        
        if (!$this->print_include_non_addressed) {
            $contacts->hasMailingAddress();
        }
        
        $this->processTransactionFilters($contacts);
        
        $contacts_not_included = Contact::whereIn('id', $not_included)
        ->orWhereNotIn('id', $contacts->pluck('id')->toArray())
        ->with('tags')
        ->withCount('mailingAddresses')
        ->orderByRaw("case when type = 'person' then last_name else company end")
        ->orderBy('first_name');
        
        $this->processTransactionFilters($contacts_not_included);
        
        $this->addTransactionCounts($contacts_not_included);
        
        $returnnotincludedpage = request()->has('page') && request()->input('page') > 1;
        
        $totalIncluded = $contacts->count();
        $totalNotIncluded = $contacts_not_included->count();
        
        $contacts = $paginate ? $contacts->simplePaginate(50) : $contacts->get();
        $contacts_not_included = $contacts_not_included->simplePaginate(50);
        $contacts_not_included->each(function($c) use ($unsubscribed_ids, $include_list_tags, $exclude_list_tags, $include_print_tags, $exclude_print_tags, $sent_between, $previously_printed, $previously_emailed, $saved_search_ids, $is_saved_search){
            $c->reasons_not_included = [
                'unsubscribed' => $unsubscribed_ids->contains($c->id),
                'list_not_included' => !empty($include_list_tags) && $c->tags->whereIn('id', $include_list_tags)->count() == 0,
                'print_not_included' => !empty($include_print_tags) && $c->tags->whereIn('id', $include_print_tags)->count() == 0,
                'list_excluded' => !empty($exclude_list_tags) && $c->tags->whereIn('id', $exclude_list_tags)->count() > 0,
                'print_excluded' => !empty($exclude_print_tags) && $c->tags->whereIn('id', $exclude_print_tags)->count() > 0,
                'recently_printed' => $sent_between->contains($c->id),
                'sent_this_print' => $previously_printed->contains($c->id),
                'sent_this_email' => $previously_emailed->contains($c->id),
                'saved_search_excluded' => $is_saved_search && !$saved_search_ids->contains($c->id),
            ];
        });
        
        $data = [
            'contacts' => $contacts,
            'contacts_not_included' => $contacts_not_included,
            'totalIncluded' => $totalIncluded,
            'totalNotIncluded' => $totalNotIncluded
        ];
        
        return $data;
    }
    
    
    /**
     * Tracks contacts that have been printed and adds the 'printed' action tag to printed contacts
     */
    public function trackPrintedContacts() {
        $data = $this->printSummary();
        
        $communicationContent = [
            'tenant_id' => array_get($this, 'tenant_id'),
            'subject' => array_get($this, 'label'),
            'content' => array_get($this, 'print_content'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $communication_content_id = DB::table('communication_contents')->insertGetId($communicationContent);
        
        // create print batch (recipient tracking)
        $contacts = $data['contacts']->pluck('id');
        $batch = $this->recipients()->max('batch')+1;
        $this->printRecipients()->attach($contacts, compact('batch', 'communication_content_id'));
        
        // 'printed' action tag
        if (isset($this->track_and_tag_events->printed)) {
            $printed_tag = $this->track_and_tag_events->printed;
            $printed_tag->contacts()->syncWithoutDetaching($contacts);
        }
        
        if ($this->include_transactions) $this->acknowledgeTransactions($contacts);
    }
    
    
    /**
     * Processes the filters set on the transaction based on the included and excluded contact Builders
     * @param  [Builder] $included_contacts 
     */
    public function processTransactionFilters(&$included_contacts) {
        if (!$this->include_transactions) return ;
        
        $options = $this->getTransactionFilters($this);
        
        $included_contacts->hasTransactionsAndSoftCredits($options);
    }
    
    /**
     * Adds transaction counts to passed in builder: 
     *      transctions: count of tax-deductible transactions (or within the requested date range if communication uses date range)
     *      transactions_not_acknowledged: count of acknowledged transactions (in range)
     *      transactions_tagged: count of tagged transactions (in range)
     * @param  [Builder] $contacts 
     */
    public function addTransactionCounts(&$contacts) {
        if (!$this->include_transactions) return ;
        
        $counts = []; 
        $transactions_func  = function ($query) {
            $query->taxDeductible();
        };
        
        if ($this->use_date_range) {
            $start = localizeDate($this->transaction_start_date, 'start');
            $end = localizeDate($this->transaction_end_date, 'end');
            $transactions_func  = function ($query) use ($start, $end) {
                $query->taxDeductible();
                $query->where('transaction_initiated_at', '>=', $start)->where('transaction_initiated_at', '<=', $end);
            };
        } 
        
        $counts['transactions'] = $transactions_func;
        
        if ($this->exclude_acknowledged_transactions) {
            $counts['transactions AS transactions_not_acknowledged'] = function ($query) use ($transactions_func) {
                $transactions_func($query);
                $query->acknowledged(false); // counting unacknowledged transactions
            };
        }
        
        if ($this->transactionTags()->count()) {
            $tag_ids = $this->transactionTags()->pluck('tag_id')->toArray();
            $counts['transactions AS transactions_tagged'] = function ($query) use ($transactions_func, $tag_ids) {
                $transactions_func($query);
                $query->taggedWithIds($tag_ids);
            };
        }
        
        if ($this->excludedTransactionTags()->count()) {
            $tag_ids = $this->excludedTransactionTags()->pluck('tag_id')->toArray();
            $counts['transactions AS excluded_transactions_tagged'] = function ($query) use ($transactions_func, $tag_ids) {
                $transactions_func($query);
                $query->notTaggedWithIds($tag_ids);
            };
        }
        
        $contacts->withCount( $counts );
    }
}
