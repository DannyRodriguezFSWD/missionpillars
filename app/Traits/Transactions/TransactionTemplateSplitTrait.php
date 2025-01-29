<?php

namespace App\Traits\Transactions;

use App\Constants;
use App\Models\TransactionTemplateSplit;

/**
 *
 * @author josemiguel
 */
trait TransactionTemplateSplitTrait {
    
    public function transactionTemplateSplitStore($transactionTemplate, $jTransactionSplit, $campaign, $chart) {
        if($transactionTemplate && $jTransactionSplit){
            $alt = $this->alternativeIdRetrieve(array_get($jTransactionSplit, 'alt_id'), TransactionTemplateSplit::class);
            if(!$alt){
                $split = mapModel(new TransactionTemplateSplit(), $jTransactionSplit);
                array_set($split, 'transaction_template_id', array_get($transactionTemplate, 'id'));
                array_set($split, 'purpose_id', array_get($chart, 'id'));
                //campaign_id can not be null because db joins return null results
                array_set($split, 'campaign_id', array_get($campaign, 'id', 1));
                if(auth()->user()->tenant->transactionTemplateSplits()->save($split)){
                    $fields = [
                        'alt_id' => array_get($jTransactionSplit, 'alt_id'), 
                        'label' => array_get($jTransactionSplit, 'name', 'Transaction Template Split'), 
                        'system_created_by' => Constants::DEFAULT_SYSTEM_CREATED_BY
                    ];
                    $alt = $this->alternativeIdCreate(array_get($split, 'id'), get_class($split), $fields);
                    return $split;
                }
            }
            $split = $alt->getRelationTypeInstance;
            
            mapModel($split, $jTransactionSplit);
            array_set($split, 'transaction_template_id', array_get($transactionTemplate, 'id'));
            array_set($split, 'purpose_id', array_get($chart, 'id'));
            //campaign_id can not be null because db joins return null results
            array_set($split, 'campaign_id', array_get($campaign, 'id', 1));
            $split->update();
            
            return $split;
        }
        
        return null;
    }
    
}
