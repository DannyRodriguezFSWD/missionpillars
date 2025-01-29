<?php

namespace App\DataTables\Scopes;

use Yajra\Datatables\Contracts\DataTableScopeContract;

class CustomContactSearchForBlankScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        if (isset($this->request->search['column_blanks'])) {
            foreach ($this->request->search['column_blanks'] as $blank) {
                if (in_array($blank, ['total_amount', 'lifetime_total', 'last_transaction_amount', 'last_transaction_date'])) {
                    $query->whereDoesntHave('transactionSplits');
                } else if (in_array($blank, ['mailing_address_1', 'mailing_address_2', 'p_o_box', 'city', 'region', 'postal_code',])) {
                    $query->where(function ($query) use ($blank) {
                        $query->whereHas('addresses', function ($q) use ($blank) {
                            $q->where('is_mailing', true)->whereNull($blank)->orWhere($blank, '');
                        })->orWhereDoesntHave('addresses');
                    });
                } else if ($blank == 'purposes') {
                    $query->whereHas('transactionSplits', function ($q) {
                        $q->whereNull('purpose_id');
                    });
                } else if ($blank == 'campaigns') {
                    $query->whereHas('transactionSplits', function ($q) {
                        $q->whereNull('campaign_id');
                    });
                } else {
                    $query->where(function ($query) use ($blank) {
                        $query->whereNull($blank)->orWhere($blank, '');
                    });
                }
            }
        }
        return $query;
    }
}
