<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSPhoneNumber extends BaseModel
{
    use SoftDeletes;
    protected $table = 'sms_phone_numbers';

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
    
    public function getNotificationContactListAttribute()
    {
        if (!empty($this->notify_to_contacts)) {
            $contacts = $this->contacts_to_notify;
            
            $list = [];
            
            foreach ($contacts as $contact) {
                $list[] = ['item' => [
                    'id' => array_get($contact, 'id'),
                    'label' => array_get($contact, 'first_name').' ' .array_get($contact, 'last_name').' ('.array_get($contact, 'email_1').')'
                ]];
            }
            
            return $list;
        } else {
            return null;
        }
    }
    
    public function getCurrentContactsAttribute()
    {
        $list = $this->notification_contact_list;
        
        if (!empty($list)) {
            $return = '';
            
            foreach ($list as $contact) {
                $return.= array_get($contact, 'item.label').', ';
            }
            
            return rtrim($return, ', ');
        } else {
            return null;
        }
    }
    
    public function getContactsToNotifyAttribute()
    {
        return Contact::withoutGlobalScopes()->whereIn('id', explode(',', $this->notify_to_contacts))->get();
    }
    
    public function getEmailsToNotifyAttribute()
    {
        $emails = [];
        
        if (!empty($this->notify_to_contacts)) {
            $contacts = $this->contacts_to_notify;
            
            foreach ($contacts as $contact) {
                if (!empty($contact->email_1 && !in_array($contact->email_1, $emails))) {
                    $emails[] = $contact->email_1;
                }
            }
        }
        
        return $emails;
    }
    
    public function getPhonesToNotifyAttribute()
    {
        $phones = [];
        
        if (!empty($this->notify_to_contacts)) {
            $contacts = $this->contacts_to_notify;
            
            foreach ($contacts as $contact) {
                if (!empty($contact->phone_numbers_only && !in_array($contact->phone_numbers_only, $phones))) {
                    $phones[] = $contact->phone_numbers_only;
                }
            }
        }
        
        return $phones;
    }
    
    public function getNameAndNumberAttribute()
    {
        if ($this->name) {
            return $this->name.' ('.$this->phone_number.')';
        } else {
            return $this->phone_number;
        }
    }
}
