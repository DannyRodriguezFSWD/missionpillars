<?php

namespace App\Traits\Transactions;

use App\Constants;
use App\Models\Contact;
use App\Models\TransactionTemplate;
use Illuminate\Support\Facades\DB;

/**
 *
 * @author josemiguel
 */
trait TransactionTemplateTrait {
    use MatchesContactPledges;

    public function transactionTemplateStore($jTemplate) {
        $alt = $this->alternativeIdRetrieve(array_get($jTemplate, 'alt_id'), TransactionTemplate::class);
        if (!$alt) {
            $transactionTemplate = mapModel(new TransactionTemplate(), $jTemplate);
            array_set($transactionTemplate, 'is_recurring', array_get($jTemplate, 'is_recurring', 0));
            array_set($transactionTemplate, 'is_pledge', array_get($jTemplate, 'is_pledge', 0));
            if (auth()->user()->tenant->transactionTemplates()->save($transactionTemplate)) {
                $fields = [
                    'alt_id' => array_get($jTemplate, 'alt_id'),
                    'label' => array_get($jTemplate, 'name', 'Transaction Template'),
                    'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
                ];
                $alt = $this->alternativeIdCreate(array_get($transactionTemplate, 'id'), get_class($transactionTemplate), $fields);
                return $transactionTemplate;
            }
            abort(500);
        }
        $transactionTemplate = $alt->getRelationTypeInstance;
        array_set($transactionTemplate, 'subscription_suspended', null);
        mapModel($transactionTemplate, $jTemplate);
        $transactionTemplate->update();

        return $transactionTemplate;
    }

    /**
     * 
     */
    public function mapTransactionToContactPledge($transaction, $chart, $campaign, $contact) 
    {
        if (get_class($contact) != Contact::class) {
            $contact = Contact::find(array_get($contact, 'id'));
        } 
        $chart_id = array_get($chart, 'id', 1);
        $campaign_id = array_get($campaign, 'id', 1);
        $pledge = $this->findFirstMatchingPledge($contact, $chart_id, $campaign_id);
        if (!is_null($pledge)) {
            $pledge->addPledgedTransaction($transaction);
        }
    }
    
}
