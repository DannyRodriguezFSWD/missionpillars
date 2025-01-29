<?php

namespace App\Http\Controllers;

use App\Classes\Shared\Transactions\SharedRecurringTransactions;
use App\Constants;
use App\Models\Campaign;
use App\Models\Purpose;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplate;
use App\Models\TransactionTemplateSplit;
use App\Traits\Transactions\Transactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringTransactionsController extends Controller {
    const STATUS_COLUMN = 'CASE
   WHEN transaction_templates.billing_cycles IS NOT NULL AND transaction_templates.billing_cycles = transaction_templates.successes THEN "complete"
   WHEN transaction_templates.subscription_terminated IS NOT NULL THEN "canceled"
   WHEN transaction_templates.subscription_suspended IS NOT NULL THEN "paused" 
   WHEN transaction_templates.status IS NULL THEN "active"
   ELSE transaction_templates.status
END';
    const BILLING_END_DATE_COLUMN = 'CASE 
        WHEN transaction_templates.subscription_terminated IS NOT NULL THEN NULL
        WHEN transaction_templates.subscription_suspended IS NOT NULL THEN NULL
		WHEN billing_period = "Day" THEN TIMESTAMPADD(DAY, billing_cycles, billing_start_date)
		WHEN billing_period = "Week" THEN TIMESTAMPADD(WEEK, billing_cycles, billing_start_date)
		WHEN billing_period = "Bi-Week" THEN TIMESTAMPADD(WEEK, 2*billing_cycles, billing_start_date)
		WHEN billing_period = "Month" THEN TIMESTAMPADD(MONTH, billing_cycles, billing_start_date)
		WHEN billing_period = "Year" THEN TIMESTAMPADD(YEAR, billing_cycles, billing_start_date)
		ELSE billing_end_date 
	END';

    use Transactions;

    private function sort($sort) {
        switch ($sort) {
            case 'status':
                $field = DB::raw(self::STATUS_COLUMN);
                break;
            case 'type':
                $field = DB::raw("CAST(transaction_template_splits.type AS CHAR)");
                break;
            case 'amount':
                $field = 'transaction_template_splits.amount';
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
            case 'billing_period':
                $field = 'transaction_templates.billing_period';
                break;
            case 'billing_end_date':
                $field = DB::raw(self::BILLING_END_DATE_COLUMN);
                break;
            default :
                $field = 'transaction_templates.id';
                break;
        }
        return $field;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if (!auth()->user()->can('transaction-view')) abort(403);
        
        $order = array_get($request, 'order', 'desc');
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        $sort = array_get($request, 'sort', 'id');

        $sorted = TransactionTemplate::join('transaction_template_splits', 'transaction_template_splits.transaction_template_id', '=', 'transaction_templates.id')
                ->join('contacts', 'contacts.id', '=', 'transaction_templates.contact_id')
                ->join('purposes', 'transaction_template_splits.purpose_id', '=', 'purposes.id')
                ->join('campaigns', 'transaction_template_splits.campaign_id', '=', 'campaigns.id')
                ->select([
                    'transaction_templates.id',
                    DB::raw(self::STATUS_COLUMN.' AS status'),
                    'transaction_templates.subscription_suspended',
                    'transaction_templates.subscription_terminated',
                    'transaction_templates.billing_cycles',
                    'transaction_templates.successes',
                    'contacts.first_name',
                    'contacts.last_name',
                    'purposes.name as chart_of_account_name',
                    'campaigns.name as campaign_name',
                    'transaction_templates.amount',
                    'transaction_template_splits.id as transaction_template_split_id',
                    DB::raw(self::BILLING_END_DATE_COLUMN.' AS billing_end_date'),
                    'transaction_templates.billing_period',
                    'transaction_templates.billing_frequency',
                ])
                ->where([
                    ['transaction_templates.is_recurring', '=', true],
                    ['transaction_templates.is_pledge', '=', false],
                    ['transaction_templates.tenant_id', '=', array_get(auth()->user(), 'tenant.id')]
                ])
                ->groupBy(['transaction_templates.id', 'purposes.name', 'campaigns.name'])
                ->orderBy($this->sort($sort), $order);
                    
        $total = $sorted->get()->count();
        $data = [
            'collection' => $sorted->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total
        ];
        
        return view('recurring_transactions.index')->with($data);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        if (!auth()->user()->can('transaction-view')) abort(403);
        
        $template = TransactionTemplate::findOrFail($id);
        $template_split = TransactionTemplateSplit::findOrFail(array_get($request, 'tts', $template->splits()->first()->id));
        $splits = TransactionSplit::whereHas('transaction', function($query) {
                            $query->where('status', 'complete');
                        })
                        ->where('transaction_template_split_id', array_get($request, 'tts'));
        
        $sum = $splits->sum('amount');

        $data = [
            'template' => $template,
            'template_split' => $template_split,
            'splits' => $splits->paginate(),
            'sum' => $sum,
            'periods' => Constants::TIME_PERIODS
        ];

        return view('recurring_transactions.show')->with($data);
    }


    public function search(Request $request) {
        $transactions = SharedRecurringTransactions::search($request);

        $sort = null;
        $order = 'asc';
        $nextOrder = 'asc';
        $total = $transactions->get();

        $data = [
            'splits' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count(),
        ];
        
        return view('recurring_transactions.index')->with($data);
    }

}
