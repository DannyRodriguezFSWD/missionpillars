<?php

namespace App\Observers;


use App\Models\TransactionSplit;
use App\Traits\TagsTrait;

/**
 * Description of ChatOfAccountObserver
 *
 * @author josemiguel
 */
class TransactionSplitsObserver {
    use TagsTrait;
    
    
    /**
     * Creates new tag if not exists and assigns 
     * tag property to Purpose
     * @param TransactionSplit $split
     */
    public function created(TransactionSplit $split) {
        if (array_get($split, 'chartOfAccount.tagInstance.id')) {
            $split->transaction->contact->tags()->sync(array_get($split, 'chartOfAccount.tagInstance.id'), false);
        }
    }
    
    public function updating(TransactionSplit $split) {
        if (array_get($split, 'chartOfAccount.tagInstance.id')) {
            $split->transaction->contact->tags()->sync(array_get($split, 'chartOfAccount.tagInstance.id'), false);
        }
    }
    
    public function updated(TransactionSplit $split) {
        if (array_get($split, 'chartOfAccount.tagInstance.id')) {
            $split->transaction->contact->tags()->sync(array_get($split, 'chartOfAccount.tagInstance.id'), false);
        }
    }
    
    public function deleted(TransactionSplit $split) {
        
    }
}
