<?php

namespace App\DataTables\Scopes;

class CustomTransactionAmountSumScope extends MPScope
{
    public function apply($query)
    {
        $request = $this->request;
        
        if (!array_get($request, 'search')) {
            return $query;
        }
        
        if (!array_get($request, 'search.transaction_amount_use_sum')) {
            return $query;
        }
        
        $min = array_get($request, 'search.transaction_amount_min') ?: -999999999;
        $max = array_get($request, 'search.transaction_amount_max');
        
        if(!is_null($max) && $max !== '') {
            $comparison = "BETWEEN $min AND $max"; 
        } else { 
            $comparison = ">= $min"; 
        }
        
        $start = $request->has('search.transaction_date_min') ? localizeDate(array_get($request, 'search.transaction_date_min'), 'start') : null;
        $end = $request->has('search.transaction_date_max') ? localizeDate(array_get($request, 'search.transaction_date_max'), 'end') : null;
        $purposes = $request->has('search.transaction_purposes') ? array_get($request, 'search.transaction_purposes') : null;
        $campaigns = $request->has('search.transaction_campaigns') ? array_get($request, 'search.transaction_campaigns') : null;
        $tags = $request->has('search.transaction_tags') ? array_get($request, 'search.transaction_tags') : null;
        
        $sql = "(( 
            select IFNULL(SUM(amount), 0) from `transaction_splits` 
            inner join `transactions` on `transactions`.`id` = `transaction_splits`.`transaction_id` 
            where `transactions`.`deleted_at` is null 
            and `transactions`.`contact_id` = `contacts`.`id` 
            and `status` = 'complete'  
            and `transaction_splits`.`deleted_at` is null 
            and (`transaction_splits`.`tenant_id` = ? or `transaction_splits`.`tenant_id` is null) ";
        
        if ($request->has('search.transaction_date_min') && $start) {
            $sql.= "and transaction_initiated_at >= '$start' ";
        }
        
        if ($request->has('search.transaction_date_max') && $end) {
            $sql.= "and transaction_initiated_at <= '$end' ";
        }

        if ($request->has('search.transaction_purposes') && $purposes) {
            $purposeIds = implode(',', $purposes);
            $sql.= "and purpose_id in ($purposeIds) ";
        }

        if ($request->has('search.transaction_campaigns') && $campaigns) {
            $campaignIds = implode(',', $campaigns);
            $sql.= "and campaign_id in ($campaignIds) ";
        }
        
        if ($request->has('search.transaction_tags') && $tags) {
            $tagIds = implode(',', $tags);
            $sql.= "and (select count(*) from tag_transaction_split tts where tts.transaction_split_id = transaction_splits.id and tts.tag_id in ($tagIds)) > 0 ";
        }
        
        $sql.= ") $comparison)";
        
        $query->whereRaw($sql,[auth()->user()->tenant_id]);
        
        return $query;
    }
}
