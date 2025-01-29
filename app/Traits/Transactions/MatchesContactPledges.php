<?php

namespace App\Traits\Transactions;

use App\Models\Contact;
use App\Models\TransactionTemplate;
use App\Models\TransactionSplit;

trait MatchesContactPledges {
    
    /**
     * [findFirstMatchingPledge description]
     * First match on campaign and purpose, 
     * If no pledges match, match on purpose. 
     * If no pledges match, match the oldest incomplete pledge
     * @param  Contact $contact     
     * @param  integer $purpose_id  
     * @param  integer $campaign_id 
     * @return TransactionTemplate               
     */
    public function findFirstMatchingPledge(Contact $contact, $purpose_id = 1, $campaign_id = 1)
    {
        // purpose and campaign
        $pledge = $contact->pledges()->incomplete()
        ->whereHas('splits', function($query) use ($purpose_id, $campaign_id) {
            $query->where([
                ['purpose_id', '=', $purpose_id],
                ['campaign_id', '=', $campaign_id]
            ]);
        })
        ->orderBy('billing_start_date')
        ->first();
        
        if (!$pledge) {
            // purpose only
            $pledge = $contact->pledges()->incomplete()
            ->whereHas('splits', function($query) use ($purpose_id) {
                $query->where([
                    ['purpose_id', '=', $purpose_id],
                ]);
            })
            ->orderBy('billing_start_date')
            ->first();
        }
        
        if (!$pledge) {
            // oldest
            $pledge = $contact->pledges()->incomplete()
            ->whereHas('splits')
            ->orderBy('billing_start_date')
            ->first();
        }
        
        return $pledge;
    }
}
