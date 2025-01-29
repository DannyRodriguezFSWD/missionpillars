<?php

namespace App\Observers;


use App\Models\Contact;

/**
 *
 * @author josemiguel
 */
class ContactsObserver {
    
    public function creating(Contact $contact) {
        $onlyNumbers = preg_replace("/[^0-9]/", '', array_get($contact, 'cell_phone'));
        $onlyNumbers = substr($onlyNumbers, -10);
        array_set($contact, 'phone_numbers_only', $onlyNumbers);
    }
    
    public function created(Contact $contact) {
        
    }
    
    public function updating(Contact $contact) {
        $onlyNumbers = preg_replace("/[^0-9]/", '', array_get($contact, 'cell_phone'));
        $onlyNumbers = substr($onlyNumbers, -10);
        array_set($contact, 'phone_numbers_only', $onlyNumbers);
    }
    
    public function updated(Contact $contact) {
        
    }
    
    public function deleted(Contact $contact) {
        
    }
}
