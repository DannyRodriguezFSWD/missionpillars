<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Classes\Mailchimp\Mailchimp;
use App\Classes\MissionPillarsLog;
use App\Models\SMSPhoneNumber;
use App\Models\Tag;
use App\Traits\Users\ContactTrait;
use App\Traits\NotificationsTrait;
use App\Observers\ContactsObserver;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Contact extends BaseModel
{
    protected $fillable = ['first_name', 'last_name', 'preferred_name', 'dob', 'email_1', 'cell_phone', 'gender', 'marital_status'];

    use SoftDeletes, ContactTrait, NotificationsTrait;
    
    protected $appends = ['full_name', 'full_name_reverse'];
    
    public static function boot() {
        parent::boot();
        Contact::observe(new ContactsObserver());
    }
    
    public function alternativeIds() {
        return $this->hasMany(ContactAltId::class);
    }
    
    public function customFieldValues() {
        return $this->morphMany(CustomFieldValue::class,'relation');
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Overrides tags method in BaseModel
     * TODO Consider porting data to taggables table and removing
     * @param [array] $tags Here for compatibility with parent method. Does nothing.
     */
    public function tags($key = null) {
        return $this->belongsToMany(Tag::class);
    }
    
    public function transactionTemplates()
    {
        return $this->hasMany(TransactionTemplate::class);
    }
    
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
    
    public function transactionSplits() {
        return $this->hasManyThrough(TransactionSplit::class, Transaction::class);
    }
    
    public function paymentOptions() {
        return $this->hasMany(PaymentOption::class);
    }
    
    public function groups() {
        return $this->belongsToMany(Group::class, 'group_contact')->withTimestamps();
    }
    
    public function leads() {
        return $this->belongsToMany(Group::class, 'contact_group_leader');
    }
    /*
    public function formEntries() {
        return $this->hasMany(FormEntry::class);
    }
    */
    public function formEntries(){
        return $this->belongsToMany(FormEntry::class, 'contact_entry', 'contact_id', 'form_entry_id')->withPivot('relationship');
    }
    
    public function checkedIn() {
        //return $this->hasManyThrough(PurchasedTicket::class, EventRegister::class, 'contact_id', 'calendar_event_contact_register_id', 'id')->where('checked_in', true);
        
        return $this->hasMany(EventRegister::class)->whereHas('tickets', function($query){
            $query->where('checked_in',  true);
        });
    }
    
    public function checkInAsVolunteer() {
        return $this->belongsToMany(CalendarEvent::class, 'calendar_event_volunteer_check_in');
    }
    
    /**
     * Search for relatives in normal mode
     * @return Array Contact::class
     */
    public function relatives() {
        return $this->belongsToMany(Contact::class, 'contact_relatives', 'contact_id', 'relative_id')
                ->withTimestamps()->withPivot(['contact_relationship', 'relative_relationship']);
    }
    
    /**
     * Search for relatives in inverse mode
     * @return Array Contact::class
     */
    public function relativesUp() {
        return $this->belongsToMany(Contact::class, 'contact_relatives', 'relative_id', 'contact_id')
                ->withTimestamps()->withPivot(['contact_relationship', 'relative_relationship']);
    }
    
    public function callMailchimpBatch($tags) {
        $source = array_pluck($this->tags, 'id');
        $si = array_intersect($source, $tags);
        $new = array_diff($tags, $source);
        $removed = array_diff($source, $si);
        
        $remove = Tag::whereIn('id', $removed)->get();
        
        $all = array_merge($source, $tags);
        $all_tags = Tag::whereIn('id', $all)->get();
        
        foreach ($all_tags as $tag){
            foreach($tag->lists as $list){
                $ids = array_pluck($list->notInTags, 'id');
                $new = array_diff($new, $ids);
            }
        }

        $mailchimp = new Mailchimp();
        $batch = [];
        foreach ($remove as $tag){
            $lists = array_pluck($tag->lists, 'mailchimp_id');
            foreach ($lists as $list) {
                $data = $mailchimp->prepareMemberData($this);
                $item = $mailchimp->prepareBatchData($this, $list, $data, 'DELETE');
                array_push($batch, $item);
            }
        }

        $subscribe = Tag::whereIn('id', $new)->get();
        foreach ($subscribe as $tag){
            $lists = array_pluck($tag->lists, 'mailchimp_id');
            foreach ($lists as $list) {
                $data = $mailchimp->prepareMemberData($this);
                $item = $mailchimp->prepareBatchData($this, $list, $data, 'PUT');
                array_push($batch, $item);
            }
        }
        
        if(count($batch) > 0){
            $operations = ['operations' => $batch];
            $response = $mailchimp->subscribeMembers($operations);
            if( $response->getStatusCode() === 200 ){
                return true;
            }
        }
        return false;
    }
    
    public function emails() {
        return $this->belongsToMany(Email::class)->withTimestamps()->withPivot('sent', 'status', 'message', 'updated_at', 'sent_at');
    }
    
    public function eventRegistered() {
        return $this->hasMany(EventRegister::class);
    }
    
    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
    
    public function pledges() {
        return $this->hasMany(TransactionTemplate::class)->where('is_pledge', true);
    }
    
    /** 
     * @deprecated it is preferred that 1 to many relations use a plural-named relationship
     */
    public function pledge() {
        MissionPillarsLog::deprecated(['message'=>'Use pledges (plural) instead']);
        return $this->pledges();
    }
    
    public function tasks() {
        return $this->hasMany(Task::class, 'linked_to')->where('status', 'open')->orderBy('id', 'desc');
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
    
    public function smsReceived()
    {
        return $this->hasMany(SMSSent::class, 'to_contact_id');
    }
    
    public function mySmsReceived()
    {
        return $this->hasMany(SMSSent::class, 'to_contact_id')->whereHas('content', function ($query) {
            $query->whereIn('sms_phone_number_from', auth()->user()->contact->SMSPhoneNumbers->pluck('phone_number')->toArray());
        });
    }

    public function smsSent()
    {
        return $this->hasMany(SMSSent::class, 'from_contact_id');
    }
    
    public function mySmsSent()
    {
        return $this->hasMany(SMSSent::class, 'from_contact_id')->whereHas('content', function ($query) {
            $query->whereIn('sms_phone_number_to', auth()->user()->contact->SMSPhoneNumbers->pluck('phone_number')->toArray());
        });
    }
    
    public function mailingAddresses() {
        return $this->addresses()->mailing();
    }
    
    public function mailingAddress() {
        return $this->morphOne(Address::class, 'relation')->mailing();
    }
    
    public function getMailingAddress() {
        return $this->mailingAddresses()->first();
    }
    
    public function unsubscribedLists()
    {
        return $this->hasMany(Unsubscribe::class);
    }
    
    public function primaryContact()
    {
        return $this->hasOne(Contact::class, 'family_id', 'family_id')->where('family_position', 'Primary Contact');
    }
    
    /** Scopes **/
    
    /**
     * Filters contacts that have an email address
     * @param  [type] $query Laravel automagically passes the query/builder
     */
    public function scopeHasEmail1($query) {
        return $query->whereNotNull('email_1')
        ->where('email_1','!=','')
        ->where('email_1','!=','NULL');
    }
    /**
     * Filters contacts that has a valid mailing Address
     * @param  [type] $query Laravel automagically passes the query/builder
     */
    public function scopeHasMailingAddress($query) {
        return $query->has('mailingAddresses');
    }
    
    /**
     * Filters contacts that have Transactions
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array  $options Optional. If specified, filters by the followning indexes: between (array with indexes start and end; both are Carbon objects), acknowleged (boolean), tax_deductible (boolean)
     */
    public function scopeHasTransactions($query, $options = []) {
        $query = $query->whereHas('transactions', function ($query) use ($options) {
            if (array_key_exists('acknowledged',$options)) {
                $query->acknowledged($options['acknowledged']);
            }
            if (array_key_exists('completed', $options)) {
                $query->completed($options['completed']);
            }
            if (array_key_exists('between', $options)) {
                $start = localizeDate(array_get($options, 'between.start')->format('Y-m-d'), 'start');
                $end = localizeDate(array_get($options, 'between.end')->format('Y-m-d'), 'end');
                $query->where('transaction_initiated_at', '>=', $start)->where('transaction_initiated_at', '<=', $end);
            }
            if (array_key_exists('tax_deductible', $options)) {
                $query->taxDeductible($options['tax_deductible']);
            }
            if (array_key_exists('tagged_with_ids', $options)) {
                $query->taggedWithIds($options['tagged_with_ids']);
            }
            if (array_key_exists('not_tagged_with_ids', $options)) {
                $query->notTaggedWithIds($options['not_tagged_with_ids']);
            }
        });
        return $query;
    }
    
    /**
     * Filters contacts that have Transactions
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  array  $options Optional. If specified, filters by the followning indexes: between (array with indexes start and end; both are Carbon objects), acknowleged (boolean), tax_deductible (boolean)
     */
    public function scopeHasTransactionsAndSoftCredits($query, $options = []) {
        $query = $query->whereHas('transactions', function ($query) use ($options) {
            if (array_key_exists('acknowledged',$options)) {
                $query->acknowledged($options['acknowledged']);
            }
            if (array_key_exists('completed', $options)) {
                $query->completed($options['completed']);
            }
            if (array_key_exists('between', $options)) {
                $start = localizeDate(array_get($options, 'between.start')->format('Y-m-d'), 'start');
                $end = localizeDate(array_get($options, 'between.end')->format('Y-m-d'), 'end');
                $query->where('transaction_initiated_at', '>=', $start)->where('transaction_initiated_at', '<=', $end);
            }
            if (array_key_exists('tax_deductible', $options)) {
                $query->where(function ($q) use ($options) {
                    $q->taxDeductible($options['tax_deductible'])->orWhereNotNull('parent_transaction_id');
                });
            }
            if (array_key_exists('tagged_with_ids', $options)) {
                $query->taggedWithIds($options['tagged_with_ids']);
            }
            if (array_key_exists('not_tagged_with_ids', $options)) {
                $query->notTaggedWithIds($options['not_tagged_with_ids']);
            }
        });
        
        return $query;
    }
    
    /**
     * Filters contacts that are marked to only receive paper contribution statements
     * @param  [type] $query Laravel automagically passes the query/builder
     * @param  boolean $only_paper_statment Optional. If specified and false, includes only contacts that are NOT marked to receive paper contribution statements
     */
    public function scopeOnlyPaperStatement($query, $only_paper_statement = true) {
        return $query->where('send_paper_contribution_statement',$only_paper_statement);
    }
    
    public function scopeHasUsPhoneNumber($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw("(length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) > 11 or length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) < 10 or (length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) = 10 and substr(REGEXP_REPLACE(cell_phone, '[+() -]', ''), 1, 1) = '1') or (length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) = 11 and substr(REGEXP_REPLACE(cell_phone, '[+() -]', ''), 1, 1) != '1')) = false");
        });
    }
    
    public function scopeHasNonUsPhoneNumber($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw("(length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) > 11 or length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) < 10 or (length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) = 10 and substr(REGEXP_REPLACE(cell_phone, '[+() -]', ''), 1, 1) = '1') or (length(REGEXP_REPLACE(cell_phone, '[+() -]', '')) = 11 and substr(REGEXP_REPLACE(cell_phone, '[+() -]', ''), 1, 1) != '1')) = true");
        });
    }
    
    public function scopeOrderByDirectorySort($query)
    {
        $sort = Session::get('directorySort', 'last_name');
        $sortType = Session::get('directorySortType', 'asc');
        
        if ($sort === 'last_name') {
            $query->orderByRaw("case when type = 'person' then last_name else company end $sortType")->orderBy('first_name');
        } elseif ($sort === 'first_name') {
            $query->orderByRaw("case when type = 'person' then first_name else company end $sortType")->orderBy('last_name');
        } else {
            $query->orderBy($sort, $sortType);
        }
    }
    
    public function getFullNameAttribute()
    {
        if ($this->type === 'person') {
            $fullName = $this->first_name;

            if ($this->middle_name) {
                $fullName.= ' '.$this->middle_name;
            }

            $fullName.= ' '.$this->last_name;
        } else {
            $fullName = $this->company;
        }

        return $fullName;
    }
    
    public function getFullNameReverseAttribute()
    {
        if ($this->type === 'person') {
            $fullName = $this->last_name;

            if ($this->middle_name) {
                $fullName.= ' '.$this->middle_name;
            }

            $fullName.= ' '.$this->first_name;
        } else {
            $fullName = $this->company;
        }

        return $fullName;
    }
    
    public function getFullAddressAttribute()
    {
        $address = $this->orderedAddresses->first();
        
        if ($address) {
            return trim($address->mailing_address_1.' '.$address->city.' '.$address->region.' '.$address->postal_code);
        } else {
            return null;
        }
    }
    
    public function getFullNameLinkAttribute()
    {
        if (auth()->user()->can('contact-update')) {
            return '<a href="'.route('contacts.show', $this).'">'.$this->full_name.'</a>';
        } else {
            return $this->full_name;
        }
    }
    
    public function getFullNameLinkShortAttribute()
    {
        if (auth()->user()->can('contact-update')) {
            return '<a href="'.route('contacts.show', $this).'" title="'.$this->full_name.'">'.str_limit($this->full_name, 13).'</a>';
        } else {
            return $this->full_name;
        }
    }
    
    public function getFullNameEmailAttribute()
    {
        $fullNameEmail = $this->full_name;
        
        if ($this->email_1) {
            $fullNameEmail.= ' ('.$this->email_1.')';
        }
        
        return $fullNameEmail;
    }
    
    public function getFullNameEmailLinkAttribute()
    {
        if (auth()->user()->can('contact-update')) {
            return '<a href="'.route('contacts.show', $this).'">'.$this->full_name_email.'</a>';
        } else {
            return $this->full_name_email;
        }
    }
    
    public function getFullNamePhoneAttribute()
    {
        $fullNamePhone = $this->full_name;
        
        if ($this->cell_phone) {
            $fullNamePhone.= ' ('.$this->cell_phone.')';
        }
        
        return $fullNamePhone;
    }
    
    public function getProfileImageSrcAttribute()
    {
        if ($this->profile_image) {
            if (env('AWS_ENABLED')) {
                return Storage::disk('s3')->has(array_get($this, 'profile_image')) ? Storage::disk('s3')->url(array_get($this, 'profile_image')) : asset('img/contact_no_profile_image.png');
            } else {
                return file_exists(storage_path('app/public/contacts/' . $this->profile_image)) ? asset('storage/contacts/'.array_get($this, 'profile_image')) : asset('img/contact_no_profile_image.png');
            }
        } else {
            return asset('img/contact_no_profile_image.png');
        }
    }
    
    public function getSMSPhoneNumbersAttribute()
    {
        return $this->SMSPhoneNumbers();
    }
    
    public function SMSPhoneNumbers()
    {
        return SMSPhoneNumber::whereRaw('find_in_set(?, notify_to_contacts)', [$this->id])->get();
    }
    
    public function getTextsAttribute()
    {
        return $this->texts();
    }
    
    public function texts()
    {
        $received = $this->mySmsReceived()->where('from_contact_id', auth()->user()->contact->id);
        $sent = $this->mySmsSent()->where('status', 'received')->where('to_contact_id', auth()->user()->contact->id);
        $sms = $received->union($sent)->orderBy('id', 'desc')->get();
        
        return $sms;
    }
    
    public function getUnreadTextsAttribute()
    {
        return $this->unreadTexts();
    }
    
    public function unreadTexts()
    {
        $received = $this->mySmsReceived();
        $sent = $this->mySmsSent();
        $sms = $received->union($sent)->where('read', 0)->get();
        
        return $sms;
    }
    
    public function isInGroup($groupId)
    {
        foreach ($this->groups as $group) {
            if (array_get($group, 'id') === $groupId) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getOrganizationNameAttribute()
    {
        if ($this->type === 'organization') {
            return $this->company;
        }
    }
    
    public function getIsUnder18Attribute()
    {
        if ($this->dob) {
            return (new Carbon($this->dob))->diffInYears(Carbon::now()) < 18;
        } else {
            return false;
        }
    }
    
    public function getAllTagsAttribute()
    {
        if ($this->tags) {
            $allTags = '';
            foreach ($this->tags as $tag) {
                $allTags.= array_get($tag, 'name').',';
            }
            return trim($allTags, ',');
        } else {
            return null;
        }
    }
    
    public function getHasBadAddressAttribute()
    {
        if ($this->tags) {
            return $this->tags->where('name', 'Bad Address')->count() > 0;
        } else {
            return false;
        }
    }
    
    public function getHasUsPhoneNumberAttribute()
    {
        $phone = onlyNumbers($this->cell_phone);
        
        if (strlen($phone) > 11 || strlen($phone) < 10 || (strlen($phone) === 10 && substr($phone, 0, 1) === '1') || (strlen($phone) === 11 && substr($phone, 0, 1) !== '1')) {
            return false;
        } else {
            return true;
        }
    }
    
    public function getUnsubscribedPhonesAttribute()
    {
        $phones = [];
        
        if ($this->unsubscribed_from_phones) {
            $ex = explode(',', $this->unsubscribed_from_phones);
            
            foreach ($ex as $phone) {
                $phones[] = $phone;
            }
        } 
        
        return $phones;
    }
    
    public function getAllNotesAttribute()
    {
        $notes = $this->notes;
        
        if ($notes->count() > 0) {
            $allNotes = '';
            
            foreach ($notes as $note) {
                $allNotes.= array_get($note, 'title');
                
                if (array_get($note, 'content')) {
                    $allNotes.= ' - '.array_get($note, 'content');
                }
                
                $allNotes.= ' - '.date('m/d/Y', strtotime(array_get($note, 'date', array_get($note, 'created_at')))).', ';
            }
            
            return rtrim($allNotes, ', ');
        } else {
            return null;
        }
    }
    
    public function getUnsubscribedListsNamesAttribute()
    {
        $lists = $this->unsubscribedLists()->with('list.datatableState')->get();
        $listsSorted = $lists->sortByDesc('list.datatableState.is_user_search')->values();
        
        if ($listsSorted->count() > 0) {
            $allNames = '';
            
            foreach ($listsSorted as $list) {
                $allNames.= array_get($list, 'list.name').', ';
            }
            
            return rtrim($allNames, ', ');
        } else {
            return null;
        }
    }
}
