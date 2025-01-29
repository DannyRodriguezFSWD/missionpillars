<?php
/* NOTE deprecated, add additional functionality to Communication */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Emails\EmailTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends BaseModel
{
    use EmailTrait,  SoftDeletes;
    protected $table = 'email_content';

    /**
     * NOTE this relation doesn't work (assumes existence of contact_email table)
     */
    // public function contacts() {
    //     return $this->belongsToMany(Contact::class)->withTimestamps()->withPivot('sent', 'status', 'message', 'updated_at');
    // }

    /**
     * NOTE this relation doesn't work. See user implementation on Communication model 
     */
    public function user() {
        return $this->belongsTo(User::class, 'created_by')->withTimestamps()->withPivot('sent', 'status', 'message', 'updated_at');
    }

    public function sent() {
        return $this->hasMany(EmailSent::class, 'email_content_id');
    }

    public function includeTags() {
        //return $this->belongsToMany(Tag::class, 'email_tags', 'email_content_id')->withPivot(['action'])->where('action', 'include');
        return $this->belongsToMany(Tag::class, 'email_include_tags', 'email_content_id');
    }

    public function excludeTags() {
        //return $this->belongsToMany(Tag::class, 'email_tags', 'email_content_id')->withPivot(['action'])->where('action', 'exclude');
        return $this->belongsToMany(Tag::class, 'email_exclude_tags', 'email_content_id');
    }

    public function lists() {
        return $this->belongsTo(Lists::class, 'list_id', 'id');

    }

    public function transactionTags()
    {
        return $this->tags('transaction');
    }
}
