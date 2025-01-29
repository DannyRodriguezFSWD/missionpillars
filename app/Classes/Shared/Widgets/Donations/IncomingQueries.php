<?php

namespace App\Classes\Shared\Widgets\Donations;

use App\Models\TransactionSplit;
/**
 * Description of IncomingQueries
 *
 * @author josemiguel
 */
class IncomingQueries {
    
    public static function fromOneTimeRunQuery($params, $start, $end) {
        $transactions = TransactionSplit::whereHas('transaction', function($query) use ($start, $end){
            $query->whereHas('template', function($q){
                $q->where([
                    ['is_recurring', '=', false],
                    ['is_pledge', '=', false]
                ]);
            })->whereBetween('transaction_initiated_at', [$start, $end]);
        })->sum('amount');
        return (double) $transactions;
    }
    
    public static function fromPledgesRunQuery($params, $start, $end) {
        $transactions = TransactionSplit::whereHas('transaction', function($query) use ($start, $end){
            $query->whereHas('template', function($q){
                $q->where([
                    ['is_recurring', '=', false],
                    ['is_pledge', '=', true]
                ]);
            })->whereBetween('transaction_initiated_at', [$start, $end]);
        })->sum('amount');
        return (double) $transactions;
    }
    
    public static function fromRecurringRunQuery($params, $start, $end) {
        $transactions = TransactionSplit::whereHas('transaction', function($query) use ($start, $end){
            $query->whereHas('template', function($q){
                $q->where([
                    ['is_recurring', '=', true],
                    ['is_pledge', '=', false]
                ]);
            })->whereBetween('transaction_initiated_at', [$start, $end]);
        })->sum('amount');
        return (double) $transactions;
    }
    
}
