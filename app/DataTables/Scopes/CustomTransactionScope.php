<?php

namespace App\DataTables\Scopes;

class CustomTransactionScope extends MPScope
{
    public function apply($query)
    {
        $request = $this->request;
        
        if (!array_get($request, 'search')) {
            return $query;
        }
        
        $start = $request->has('search.transaction_date_min') ? localizeDate(array_get($request, 'search.transaction_date_min'), 'start') : null;
        $end = $request->has('search.transaction_date_max') ? localizeDate(array_get($request, 'search.transaction_date_max'), 'end') : null;
        $min = $request->has('search.transaction_amount_min') ? array_get($request, 'search.transaction_amount_min') : null;
        $max = $request->has('search.transaction_amount_max') ? array_get($request, 'search.transaction_amount_max') : null;
        $useSum = array_get($request, 'search.transaction_amount_use_sum', 1);
        $purposes = $request->has('search.transaction_purposes') ? array_get($request, 'search.transaction_purposes') : null;
        $campaigns = $request->has('search.transaction_campaigns') ? array_get($request, 'search.transaction_campaigns') : null;
        $tags = $request->has('search.transaction_tags') ? array_get($request, 'search.transaction_tags') : null;
        
        if (!$start && !$end && !$min && !$max && !$purposes && !$campaigns && !$tags) {
            return $query;
        }
        
        $query->whereHas('transactionSplits', function ($q) use ($request, $start, $end, $min, $max, $useSum, $purposes, $campaigns, $tags) {
            $q->completed();
            
            if (!$useSum) {
                if ($request->has('search.transaction_amount_min') && $min) {
                    $q->where('amount','>=', $min);
                } 

                if($request->has('search.transaction_amount_max') && $max) {
                    $q->where('amount','<=', $max); 
                }
            }
            
            if ($request->has('search.transaction_date_min') && $start) {
                $q->where('transaction_initiated_at','>=', $start);
            }

            if ($request->has('search.transaction_date_max') && $end) {
                $q->where('transaction_initiated_at','<=', $end);
            }

            if ($request->has('search.transaction_purposes') && $purposes) {
                $q->whereIn('purpose_id', $purposes);
            }
            
            if ($request->has('search.transaction_campaigns') && $campaigns) {
                $q->whereIn('campaign_id', $campaigns);
            }
            
            if ($request->has('search.transaction_tags') && $tags) {
                $q->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('id', $tags);
                });
            }
        });
        
        $query->with(['transactionSplits' => function ($q) use ($request, $start, $end, $min, $max, $useSum, $purposes, $campaigns, $tags) { 
            $q->completed();
        
            if (!$useSum) {
                if ($request->has('search.transaction_amount_min') && $min) {
                    $q->where('amount','>=', $min);
                } 
            
                if($request->has('search.transaction_amount_max') && $max) {
                    $q->where('amount','<=', $max); 
                }
            }
            
            if ($request->has('search.transaction_date_min')) {
                $q->where('transaction_initiated_at','>=', $start);
            }

            if ($request->has('search.transaction_date_max')) {
                $q->where('transaction_initiated_at','<=', $end);
            }
            
            if ($request->has('search.transaction_purposes') && $purposes) {
                $q->whereIn('purpose_id', $purposes);
            }
            
            if ($request->has('search.transaction_campaigns') && $campaigns) {
                $q->whereIn('campaign_id', $campaigns);
            }
            
            if ($request->has('search.transaction_tags') && $tags) {
                $q->whereHas('tags', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('id', $tags);
                });
            }
        }]);
        
        return $query;
    }
}
