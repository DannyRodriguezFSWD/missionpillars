<?php

namespace App\Observers\Transactions;

use App\Constants;
use App\Models\Transaction;
use App\Traits\TagsTrait;
use App\Classes\Email\Mailgun\PledgeEmailNotification;

/**
 * Description of ChatOfAccountObserver
 *
 * @author josemiguel
 */
class TransactionsObserver {

    use TagsTrait;

    public function created(Transaction $transaction) {
        $tag = $this->tagExists(array_get(Constants::TAG_SYSTEM, 'TAGS_BY_NAME.PLEDGER'), array_get(Constants::TAG_SYSTEM, 'FOLDERS.ALL_TAGS'));
        if (array_get($transaction, 'template.is_pledge', false) && !is_null($tag)) {
            $transaction->contact->tags()->sync(array_get($tag, 'id'), false);
        }
    }

    public function updating(Transaction $transaction) {
        
    }

    public function updated(Transaction $transaction) {
        
    }

    public function deleted(Transaction $transaction) {
        
    }
    
    public function pledgeTransaction(Transaction $transaction) {
        $pledge = array_get($transaction, 'pledge');
        //if is pledge, queue an email if settings are set to true
        if ( !is_null($pledge) && count($pledge) > 0 ) {
            $notification = new PledgeEmailNotification();
            $notification->run('pledge-transaction-created', ['transaction' => $transaction]);
            unset($notification);
        }
    }

}
