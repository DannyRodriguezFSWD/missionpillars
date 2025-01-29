<?php

namespace App\Classes\Shared\Transactions;

use Illuminate\Support\Facades\DB;
use App\Models\TransactionSplit;
use App\Models\Contact;

/**
 * Description of SharedTransactions
 *
 * @author josemiguel
 */
class SharedTransactions {

    public function __construct() {

    }

    public function __destruct() {

    }

    private static function sort($sort) {
        switch ($sort) {
            case 'status':
                $field = DB::raw("CAST(transactions.status AS CHAR)");
                break;
            case 'type':
                $field = DB::raw("CAST(transaction_splits.type AS CHAR)");
                break;
            case 'for':
                $field = 'purposes.name';
                break;
            case 'campaign':
                $field = 'campaigns.name';
                break;
            case 'contact':
                $field = 'contacts.first_name';
                break;
            case 'card':
                $field = 'payment_options.card_type';
                break;
            case 'amount':
                $field = 'transaction_splits.amount';
                break;
            default :
                $field = 'transactions.transaction_initiated_at';
                break;
        }
        return $field;
    }

    private static function transactions($wheres = []) {
        $where = [
            ['transaction_splits.tenant_id', '=', auth()->user()->tenant->id],
            ['transaction_templates.is_pledge', '=', false],
            ['transaction_splits.deleted_at', '=', null],
            ['transactions.status', '!=', 'stub']
        ];

        foreach ($wheres as $w) {
            array_push($where, $w);
        }

        $builder = TransactionSplit::with(['purpose', 'campaign', 'transaction', 'transaction.contact', 'transaction.paymentOption', 'transaction.template', 'tags.folder'])
                ->join('purposes', 'transaction_splits.purpose_id', '=', 'purposes.id')
                ->join('campaigns', 'transaction_splits.campaign_id', '=', 'campaigns.id')
                ->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                ->join('transaction_templates', 'transaction_templates.id', '=', 'transactions.transaction_template_id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('payment_options', 'transactions.payment_option_id', '=', 'payment_options.id')
                ->select('transaction_splits.*', 'transactions.transaction_last_updated_at', 'transaction_templates.is_recurring')
                ->where($where);

        return $builder;
    }

    /**
     * Get all transaction related to params
     * @param \App\Classes\Shared\Transactions\Contact $contact
     * @param array $order
     * @return Illuminate\Database\Eloquent\Builder
     */
    public static function all($field = 'id', $order = 'desc', Contact $contact = null, $statement = null) {
        if (!$contact) {//general transactions
            return self::transactions()->orderBy(self::sort($field), $order);
        }
        //contact transactions
        $where = [
            ['transactions.contact_id', '=', array_get($contact, 'id')]
        ];

        if(!is_null($statement)){
            return self::transactions($where)
                    ->whereBetween('transactions.transaction_initiated_at', [array_get($statement, 'start_date'), array_get($statement, 'end_date')])
                    ->orderBy(self::sort($field), $order);
        }

        return self::transactions($where)->orderBy(self::sort($field), $order);
    }

    public static function search($request = null, Contact $contact = null) 
    {
        $transactions = self::transactions();
        
        if (!is_null(array_get($request, 'email'))) {
            $transactions->where('contacts.email_1', '=', array_get($request, 'email'));
        }

        $start = setUTCDateTime(array_get($request, 'start').' 00:00:00');
        $end = setUTCDateTime(array_get($request, 'end').' 23:59:59');
        
        if (array_get($request, 'start') != '' && array_get($request, 'end') != '') {
            $transactions->whereBetween('transactions.transaction_initiated_at', [$start, $end]);
        }

        if (!empty(array_get($request, 'start')) && empty(array_get($request, 'end'))){
            $transactions->where('transactions.transaction_initiated_at','>=', $start);
        }

        if (empty(array_get($request, 'start')) && !empty(array_get($request, 'end'))){
            $transactions->where('transactions.transaction_initiated_at','<=', $end);
        }

        if (!is_null(array_get($request, 'status')) && strtolower(array_get($request, 'status')) != 'all') {
            $transactions->where('transactions.status', array_get($request, 'status'));
        }
        
        if (!is_null(array_get($request, 'payment_category')) && strtolower(array_get($request, 'payment_category')) != 'all') {
            $transactions->where('payment_options.category', array_get($request, 'payment_category'));
        }

        if (count(array_get($request, 'chart', [])) > 0) {
            $transactions->whereIn('transaction_splits.purpose_id', array_get($request, 'chart', []));
        }

        if (count(array_get($request, 'campaign', [])) > 0) {
            $transactions->whereIn('transaction_splits.campaign_id', array_get($request, 'campaign', []));
        }

        if (array_has($request, 'promised_pay_date') && !is_null(array_get($request, 'promised_pay_date'))) {
            $transactions->where('transactions.promised_pay_date', array_get($request, 'promised_pay_date'));
        }

        if (!is_null(array_get($request, 'keyword'))) {
            $transactions->where(function($query) use ($request) {
                $query->whereRaw("CONCAT(IFNULL(contacts.first_name,''), ' ', IFNULL(contacts.last_name,''), ' ', IFNULL(contacts.company,'')) like ?", ['%'.array_get($request, 'keyword').'%']);
            });
        }

        if(!is_null($contact)){
            $transactions->where('transactions.contact_id', array_get($contact, 'id'));
        }
        
        if (!is_null(array_get($request, 'online_offline')) && strtolower(array_get($request, 'online_offline')) != 'all') {
            if (array_get($request, 'online_offline') === 'online') {
                $transactions->where('transactions.system_created_by', 'Continue to Give');
            } elseif (array_get($request, 'online_offline') === 'offline') {
                $transactions->where(function ($onlineOfflineQuery) {
                    $onlineOfflineQuery->where('transactions.system_created_by', '<>', 'Continue to Give')->orWhereNull('transactions.system_created_by');
                });
            }
        }
        
        if (count(array_get($request, 'channel', [])) > 0) {
            $transactions->whereIn('channel', array_get($request, 'channel'));
        }

        if (!empty(array_get($request, 'tags'))) {
            $transactions->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('id', array_get($request, 'tags'));
            });
        }
        
        if (array_get($request, 'transaction_id', false)) {
            $transactions->where('transactions.id', array_get($request, 'transaction_id'));
        }
        
        $startCreatedAt = setUTCDateTime(array_get($request, 'startCreatedAt').' 00:00:00');
        $endCreatedAt = setUTCDateTime(array_get($request, 'endCreatedAt').' 23:59:59');
        
        if (array_get($request, 'startCreatedAt') != '' && array_get($request, 'endCreatedAt') != '') {
            $transactions->whereBetween('transactions.created_at', [$startCreatedAt, $endCreatedAt]);
        }

        if (!empty(array_get($request, 'startCreatedAt')) && empty(array_get($request, 'endCreatedAt'))){
            $transactions->where('transactions.created_at','>=', $startCreatedAt);
        }

        if (empty(array_get($request, 'startCreatedAt')) && !empty(array_get($request, 'endCreatedAt'))){
            $transactions->where('transactions.created_at','<=', $endCreatedAt);
        }
        
        $startDepositDate = setUTCDateTime(array_get($request, 'startDepositDate').' 00:00:00');
        $endDepositDate = setUTCDateTime(array_get($request, 'endDepositDate').' 23:59:59');
        
        if (array_get($request, 'startDepositDate') != '' && array_get($request, 'endDepositDate') != '') {
            $transactions->whereBetween('transactions.deposit_date', [$startDepositDate, $endDepositDate]);
        }

        if (!empty(array_get($request, 'startDepositDate')) && empty(array_get($request, 'endDepositDate'))){
            $transactions->where('transactions.deposit_date','>=', $startDepositDate);
        }

        if (empty(array_get($request, 'startDepositDate')) && !empty(array_get($request, 'endDepositDate'))){
            $transactions->where('transactions.deposit_date','<=', $endDepositDate);
        }
        
        return $transactions->orderBy(self::sort(array_get($request, 'sort')), array_get($request, 'order', 'desc'))->orderBy('transactions.id', 'desc');
    }

}
