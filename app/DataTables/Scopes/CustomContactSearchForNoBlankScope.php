<?php

namespace App\DataTables\Scopes;

use Yajra\Datatables\Contracts\DataTableScopeContract;

class CustomContactSearchForNoBlankScope extends MPScope
{
    /**
     * Apply a query scope.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function apply($query)
    {
        if (isset($this->request->search['column_no_blanks'])) {
            foreach ($this->request->search['column_no_blanks'] as $blank) {
                if (in_array($blank, ['total_amount', 'lifetime_total', 'last_transaction_amount', 'last_transaction_date'])) {
                    $query->whereHas('transactionSplits');
                } else if (in_array($blank, ['mailing_address_1', 'mailing_address_2', 'p_o_box', 'city', 'region', 'postal_code'])) {
                    $query->where(function ($query) use ($blank) {
                        $query->whereHas('addresses', function ($q) use ($blank) {
                            $q->where('is_mailing', true)->whereNotNull($blank)->where($blank, '!=', '');
                        });
                    });
                } else if ($blank == 'purposes') {
                    $query->whereHas('transactionSplits', function ($q) {
                        $q->whereNotNull('purpose_id');
                    });
                } else if ($blank == 'campaigns') {
                    $query->whereHas('transactionSplits', function ($q) {
                        $q->whereNotNull('campaign_id');
                    });
                } else {
                    $query->where(function ($query) use ($blank) {
                        $query->whereNotNull($blank)->where($blank, '!=', '');
                    });
                }
            }
        }
        return $query;
    }
}
