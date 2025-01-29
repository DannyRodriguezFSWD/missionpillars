<?php

namespace App\DataTables\Scopes;

class CustomLatentDonorsScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $notGiveStart = localizeDate(array_get($this->request, 'search.latent_not_give_from_date'), 'start');
        $notGiveEnd = localizeDate(array_get($this->request, 'search.latent_not_give_to_date'), 'end');
        
        if ($notGiveStart || $notGiveEnd) {
            $query->whereDoesntHave('transactions', function ($q) use ($notGiveStart, $notGiveEnd) { 
                $q->completed();
                if ($notGiveStart) {
                    $q->where('transaction_initiated_at','>=', $notGiveStart);
                }
                if ($notGiveEnd) {
                    $q->where('transaction_initiated_at','<=', $notGiveEnd);
                }
            });
        }
        
        $gaveStart = localizeDate(array_get($this->request, 'search.latent_gave_from_date'), 'start');
        $gaveEnd = localizeDate(array_get($this->request, 'search.latent_gave_to_date'), 'end');
        
        if ($gaveStart || $gaveEnd) {
            $query->whereHas('transactions', function ($q) use ($gaveStart, $gaveEnd) { 
                $q->completed();
                if ($gaveStart) {
                    $q->where('transaction_initiated_at','>=', $gaveStart);
                }
                if ($gaveEnd) {
                    $q->where('transaction_initiated_at','<=', $gaveEnd);
                }
            });
        }
        
        return $query;
    }
}
