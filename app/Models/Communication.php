<?php

namespace App\Models;

use App\Traits\CommunicationTrait;
use App\Classes\MissionPillarsLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Communication extends BaseModel
{
    use CommunicationTrait, SoftDeletes;
    
    protected $auto_save_tenant_id = true;
    protected $appends = [
        'transaction_tags',
        'excluded_transaction_tags',
        'include_tags','exclude_tags',
        'print_include_tags','print_exclude_tags'
    ];

    /** Relations **/
    
    // Recipients
    
    /**
     * Recipients of this communication
     * TODO Consider generalizing this method and include a type pivot and implement emailRecipients
     * @return [Relation] contacts that have been emailed with metadata
     */
    public function recipients($options = []) {
        $query = $this->belongsToMany(Contact::class)->withTimestamps()->withPivot('batch');
        if (in_array('between', $options)) {
            extract($options['between']);
            $query->whereBetween('communication_contact.updated_at', 
            [$start->startOfDay(), $end->endOfDay()]);
        }
        return $query;
    }
    
    /**
     * TODO generalize and filter by email type
     * Email recipients of this communication
     * @return [Relation] contacts that have been emailed with metadata
     */
    // public function emailRecipients() {
    //     return $this->belongsToMany(Contact::class)->withTimestamps()->withPivot('sent', 'status', 'message', 'updated_at');
    // }
    
    /**
     * Print recipients of this communication
     * TODO if contacts is made to be more generalized (including a 'type' pivot column) modify this to scope to print communications and create a mutator (see tag mutators below)
     * @return [Relation] contacts that have been included in a print communication
     */
    public function printRecipients($options = []) {
        return $this->recipients($options);
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    // Email sent tracking
    
    /**
     * Deprecated use sentEmails relation instead
     * @return [Relation] 
     */
    public function sent() {
        MissionPillarsLog::log([
            'event' => 'Deprecated',
            'caller_function'=>implode('::',[get_class($this),__FUNCTION__]),
            'message'=>'Deprecated use sentEmails relation instead',
            'url'=> url()->current(),
            'data'=>json_encode([
                'backtrace'=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ]),
        ]);
        return $this->sentEmails();
    }
    
    public function sentEmails() {
        return $this->hasMany(EmailSent::class, 'email_content_id');
    }

    
    // Tags 
    /// NOTE also see tags relation on BaseModel
    
    public function includeTags() {
        return $this->belongsToMany(Tag::class, 'email_include_tags', 'email_content_id');
    }

    public function excludeTags() {
        return $this->belongsToMany(Tag::class, 'email_exclude_tags', 'email_content_id');
    }

    public function transactionTags()
    {
        return $this->tags('transaction');
    }
    
    public function excludedTransactionTags()
    {
        return $this->tags('excluded_transaction');
    }
    
    public function printIncludeTags()
    {
        return $this->tags('print_include');
    }
    
    public function printExcludeTags()
    {
        return $this->tags('print_exclude');
    }
    
    
    // List
    
    public function list() {
        return $this->belongsTo(Lists::class, 'list_id', 'id');
    }
    
    /**
     * Deprecated use list relation instead
     * @return [Relation] 
     */
    public function lists() {
        MissionPillarsLog::log([
            'event' => 'Deprecated',
            'caller_function'=>implode('::',[get_class($this),__FUNCTION__]),
            'message'=>'Deprecated use list (singular) relation instead',
            'url'=> url()->current(),
            'data'=>json_encode([
                'backtrace'=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ]),
        ]);
        return $this->list();
    }
    
    // User Tracking 
    
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    
    
    /** Accessors **/
    
    /**
     * Allows treating track_and_tag_events as an object with Tag objects as properties 
     * named by the various actions
     * @param  [string|null] $value ELoquent automagically passes the stored DB value (JSON string or NULL)
     * @return [object|null]        
     */
    public function getTrackAndTagEventsAttribute($value) {
        $object = json_decode($value);
        if ($object) {
            foreach ($object as $action => $id) {
                $object->$action = Tag::withoutGlobalScopes()->find($id);
                if ($object->$action) $object->$action->load('folder');
            }
        }
        return $object;
    }
    
    /** 
     * various accessors for keyed tags relation 'attributes' 
     */
    public function getTransactionTagsAttribute() { return $this->transactionTags()->get(); }
    public function getExcludedTransactionTagsAttribute() { return $this->excludedTransactionTags()->get(); }
    public function getIncludeTagsAttribute() { return $this->includeTags()->get(); }
    public function getExcludeTagsAttribute() { return $this->excludeTags()->get(); }
    public function getPrintIncludeTagsAttribute() { return $this->printIncludeTags()->get(); }
    public function getPrintExcludeTagsAttribute() { return $this->printExcludeTags()->get(); }
    
    public function getPublicLinkAttribute()
    {
        if (empty($this->uuid)) {
            return null;
        } else {
            return sprintf(env('APP_DOMAIN'), $this->tenant->subdomain) . implode('/', ['communications', $this->uuid, 'public']);
        }
    }
    
    public function getTotalScheduledEmailsAttribute() 
    {
        if ($this->time_scheduled && strtotime($this->time_scheduled) > strtotime(date('Y-m-d H:i:s'))) {
            return $this->sentEmails()->where('sent', 0)->count();
        } else {
            return 0;
        }
    }
    
    public function getHasNotBeenSentAttribute() 
    {
        $totalemails = $this->sentEmails()->count();
        $totalprinted = $this->printRecipients()->count();
        
        return $totalemails === 0 && $totalprinted === 0;
    }
    
    /** Scopes **/
    public function scopeIncludesTransactions($query) {
        return $query->where('include_transactions',1);
    }
    
    
    /** Mutators **/
    
    /**
     * Allows setting the track_and_tag_events attribute using a json string, null, or an object contating Tag objects
     * @param [string|null|object] $value 
     */
    public function setTrackAndTagEventsAttribute($value) {
        if (is_string($value) || is_null($value)) $this->attributes['track_and_tag_events'] = $value;
        elseif (is_object($value) || is_array($value)) {
            foreach ($value as $action => $tag) {
                array_set($value, $action, array_get($tag, 'id'));
            }
            $this->attributes['track_and_tag_events'] = json_encode($value);
        } else {
            \Log::warning("Attempt to store value with unexpected type (".gettype($value).") for Communication::track_and_tag_events \n");
            \Log::info($value);
        }
    }
    
    /** 
     * various mutators for keyed tags relation 'attributes' 
     */
    public function setIncludeTagsAttribute(array $values) { $this->includeTags()->sync($values); }
    public function setExcludeTagsAttribute(array $values) { $this->excludeTags()->sync($values); }
    
    public function setTransactionTagsAttribute($tagids = []) { return $this->syncTags($tagids, 'transaction'); }
    public function setExcludedTransactionTagsAttribute($tagids = []) { return $this->syncTags($tagids, 'excluded_transaction'); }
    public function setPrintIncludeTagsAttribute($tagids = []) { return $this->syncTags($tagids, 'print_include'); }
    public function setPrintExcludeTagsAttribute($tagids = []) { return $this->syncTags($tagids, 'print_exclude'); }
}
