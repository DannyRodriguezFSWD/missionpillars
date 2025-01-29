<?php

namespace App\DataTables\Scopes;

class GlobalSearchTransactionAmountSumScope extends MPScope
{
    public function apply($query)
    {
        \Log::info($query->toSql());
        return $query;
        // return $query->orHavingRaw('SUM(transaction_splits.amount) LIKE "%?%"',[array_get($this->request, 'search.value')]);
        return $query->orWhereRaw('contacts.id IN ( SELECT contact_id FROM transaction_splits JOIN transactions GROUP BY contact_id HAVING SUM(amount) LIKE "%?%" )',[array_get($this->request, 'search.value')]);
        // return $query->whereHas('transactionSplits', function ($query) { $query->where('amount','>',15); });
    }
}
