<?php

namespace App\Traits;

use App\Models\Contact;


/**
 *
 * @author josemiguel
 */
trait CardPaymentsTrait {

    /**
     * tries to connect with gateway to create card and get the id associated
     */
    public function getCardId(){
        
    }
    
/*
    public function hasStripeId(){
        if(get_class($this) == Contact::class){
            return false;
        }

        return !is_null(array_get($this, 'stripe_id'));
    }
*/
}
