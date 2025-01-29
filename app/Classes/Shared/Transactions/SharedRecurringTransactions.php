<?php

namespace App\Classes\Shared\Transactions;

use Illuminate\Support\Facades\DB;
use App\Models\TransactionTemplateSplit;
use App\Models\Contact;

/**
 * Description of SharedTransactions
 *
 * @author josemiguel
 */
class SharedRecurringTransactions {

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    public static function sort($sort) {
        switch ($sort) {
            case 'successes':
                $field = DB::raw("transaction_templates.successes");
                break;
            case 'type':
                $field = DB::raw("CAST(transaction_template_splits.type AS CHAR)");
                break;
            case 'cycle':
                $field = 'transaction_templates.billing_cycles';
                break;
            case 'for':
                $field = 'purposes.name';
                break;
            case 'campaign':
                $field = 'campaigns.name';
                break;
            case 'frequency':
                $field = DB::raw('transaction_templates.billing_frequency');
                break;
            case 'remaining':
                $field = DB::raw('difference, transaction_templates.billing_cycles');
                break;
            default :
                $field = 'transaction_template_splits.amount';
                break;
        }
        return $field;
    }

    private static function transactions($wheres = []) {
        $where = [
            ['transaction_template_splits.tenant_id', '=', auth()->user()->tenant->id],
            ['transaction_templates.is_pledge', '=', false],
            ['transaction_template_splits.deleted_at', '=', null],
            ['transactions.status', '!=', 'stub'],
            ['transaction_templates.is_recurring', '=', true]
        ];

        foreach ($wheres as $w) {
            array_push($where, $w);
        }

        $builder = TransactionTemplateSplit::join('purposes', 'transaction_template_splits.purpose_id', '=', 'purposes.id')
                ->join('transaction_templates', 'transaction_templates.id', '=', 'transaction_template_splits.transaction_template_id')
                ->join('transactions', 'transactions.transaction_template_id', '=', 'transaction_templates.id')
                ->join('contacts', 'contacts.id', '=', 'transactions.contact_id')
                ->join('campaigns', 'transaction_template_splits.campaign_id', '=', 'campaigns.id')
                ->select('transaction_template_splits.*', 'purposes.name as coa_name', 'campaigns.name as c_name', DB::raw('(transaction_templates.billing_cycles - transaction_templates.successes) as difference'))
                ->where($where);

        return $builder;
    }

    /**
     * Get all transaction related to params
     * @param \App\Classes\Shared\Transactions\Contact $contact
     * @param array $order
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function all($field = 'id', $order = 'desc', Contact $contact = null) {
        if (!$contact) {//general transactions
            return self::transactions()->orderBy(self::sort($field), $order);
        }
        //contact transactions
        $where = [
            ['transactions.contact_id', '=', array_get($contact, 'id')]
        ];
        return self::transactions($where)->orderBy(self::sort($field), $order);
    }

    public static function show(TransactionTemplateSplit $transactionTemplateSplit = null, $field = 'id', $order = 'desc') {
        $where = [
            ['transactions.tenant_id', '=', auth()->user()->tenant->id],
            ['transaction_splits.deleted_at', '=', null],
            ['transactions.status', '!=', 'stub']
        ];
        $transactions = $transactionTemplateSplit->transactionSplits()
                ->join('purposes', 'transaction_splits.purpose_id', '=', 'purposes.id')
                ->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                ->join('transaction_templates', 'transaction_templates.id', '=', 'transactions.transaction_template_id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transaction_splits.*', 'transactions.transaction_last_updated_at')
                ->where($where);
        return $transactions;
    }

    public static function search($request = null, Contact $contact = null) {
        $transactions = self::transactions();

        if (!is_null(array_get($request, 'email'))) {
            $transactions->where('contacts.email_1', '=', array_get($request, 'email'));
        }

        $start = array_get($request, 'start');
        $end = array_get($request, 'end');

        if ($start != '' && $end != '') {
            $start .= ' 00:00:00';
            $end .= ' 23:59:59';
            $transactions->whereBetween('transactions.transaction_initiated_at', [$start, $end]);
        }

        if (!is_null(array_get($request, 'status')) && strtolower(array_get($request, 'status')) != 'all') {
            dd(array_get($request, 'status'));
            $transactions->where('transactions.status', array_get($request, 'status'));
        }

        if ((int) array_get($request, 'chart', 0) > 0) {
            $transactions->where('transaction_splits.purpose_id', array_get($request, 'chart'));
        }

        if ((int) array_get($request, 'campaign', 0) > 0) {
            $transactions->where('transaction_splits.campaign_id', array_get($request, 'campaign'));
        }

        if (array_has($request, 'promised_pay_date') && !is_null(array_get($request, 'promised_pay_date'))) {
            $transactions->where('transactions.promised_pay_date', array_get($request, 'promised_pay_date'));
        }

        if (!is_null(array_get($request, 'keyword'))) {
            $transactions->where(function($query) use ($request) {
                $query->where('contacts.first_name', 'like', '%' . array_get($request, 'keyword') . '%')
                        ->orWhere('contacts.last_name', 'like', '%' . array_get($request, 'keyword') . '%');
            });
        }

        return $transactions->orderBy('id', 'desc');
    }

}
