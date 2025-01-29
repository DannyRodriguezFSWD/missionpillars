<?php

namespace App\DataTables\Scopes;

class TransactionCompletedScope extends MPScope
{
    public function apply($query)
    {
        $query->with(['transactionSplits' => function ($q) { 
            $q->completed();
        }]);
        
        return $query;
    }
}
