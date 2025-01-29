<?php

namespace App\Observers;


use App\Models\EmailSent;
use Ramsey\Uuid\Uuid;

/**
 *
 * @author josemiguel
 */
class EmailSentObserver {
    
    public function creating(EmailSent $sent) {
        array_set($sent, 'uuid', Uuid::uuid4());
    }
    
}
