<?php

namespace App\Traits;
use App\Models\Email;

/**
 *
 * @author josemiguel
 */
trait NotificationsTrait {
    
    public function notification($type = 'email', $message = []) {
        if($type === 'email'){
            $this->email($message);
        }
    }
    
    public function email($message) {
        $email = new Email();
        array_set($email, 'subject', array_get($message, 'subject'));
        array_set($email, 'content', array_get($message, 'content'));
        array_set($email, 'relation_id', array_get($this, 'id'));
        array_set($email, 'relation_type', get_class($this));
        array_set($email, 'tenant_id', array_get($this, 'tenant_id'));
        
        if ($email->save()) {
            $this->sendEmail($email);
        }
    }
    
}
