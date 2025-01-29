<?php

namespace App\DataTables\Scopes;

class CustomRecurringDonorsScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        $recurringDonors = array_get($this->request, 'search.recurring_donors');
        
        if ($recurringDonors) {
            $query->whereHas('transactionTemplates', function ($query) {
                $query->where('is_recurring', 1)
                        ->whereNull('subscription_suspended')
                        ->whereNull('subscription_terminated')
                        ->whereRaw('ifnull(successes, 0) < billing_cycles');
            });
        }
        
        $exRecurringDonors = array_get($this->request, 'search.ex_recurring_donors');
        
        if ($exRecurringDonors) {
            $query->whereHas('transactionTemplates', function ($query) {
                $query->where('is_recurring', 1)->where(function ($query) {
                    $query->whereNotNull('subscription_suspended')->orWhereNotNull('subscription_terminated');
                });
            })->whereDoesntHave('transactionTemplates', function ($query) {
                $query->where('is_recurring', 1)
                        ->whereNull('subscription_suspended')
                        ->whereNull('subscription_terminated')
                        ->whereRaw('ifnull(successes, 0) < billing_cycles');
            });
        }
        
        $nonRecurringDonors = array_get($this->request, 'search.non_recurring_donors');
        
        if ($nonRecurringDonors) {
            $query->whereDoesntHave('transactionTemplates', function ($query) {
                $query->where('is_recurring', 1);
            });
        }
        
        return $query;
    }
}
