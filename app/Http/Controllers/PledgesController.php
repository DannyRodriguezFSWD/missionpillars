<?php

namespace App\Http\Controllers;

use App\Classes\Email\Mailgun\PledgeEmailNotification;
use App\Classes\Shared\Widgets\Charts\Pie\PieChart;
use App\Constants;
use App\Http\Requests\Pledges;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\PaymentOption;
use App\Models\Purpose;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplate;
use App\Models\TransactionTemplateSplit;
use App\Models\User;
use App\Traits\Transactions\Transactions as TransactionsTrait;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PledgesController extends Controller {

    use TransactionsTrait;
    use \App\Traits\GetsPurposesWithChildren;
    
    const PERMISSION = 'crm-pledges';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(auth()->check()){ // Allows for public routes/methods (e.g., cancel, cancelPledge, canceled)
				if(!auth()->user()->tenant->can(self::PERMISSION)){
					return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
				}
			}
            return $next($request);
        });
    }

    private function sort($sort) {
        switch ($sort) {
            case 'status':
                $field = DB::raw("CAST(transaction_templates.status AS CHAR)");
                break;
            case 'type':
                $field = DB::raw("CAST(transaction_splits.type AS CHAR)");
                break;
            case 'date':
                $field = 'transactions.updated_at';
                break;
            case 'for':
                $field = 'purposes.name';
                break;
            case 'contact':
                $field = 'contacts.first_name';
                break;
            case 'card':
                $field = 'payment_options.card_type';
                break;
            case 'amount':
                $field = 'transaction_template_splits.amount';
                break;
            default :
                $field = 'transaction_template_splits.id';
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
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $sort = array_get($request, 'sort', 'id');
        $order = array_get($request, 'order', 'desc');
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        $field = $this->sort($sort);
        
            
        $sorted = TransactionTemplate::join('transaction_template_splits', 'transaction_template_splits.transaction_template_id', '=', 'transaction_templates.id')
                ->join('purposes', 'transaction_template_splits.purpose_id', '=', 'purposes.id')
                ->join('campaigns', 'transaction_template_splits.campaign_id', '=', 'campaigns.id')
                ->join('contacts', 'transaction_templates.contact_id', '=', 'contacts.id')
                ->select(['transaction_templates.id'])
                ->where('transaction_templates.tenant_id', array_get(auth()->user(), 'tenant.id'))
                ->where('transaction_templates.is_pledge', true)
                ->orderBy($field, $order);
            
        $total = $sorted->get()->count();
        $data = [
            'collection' => $sorted->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total
        ];
        return view('pledges.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $pledge = null;
        if (array_has($request, 'pledge')) {
            $pledge = TransactionTemplateSplit::findOrFail(array_get($request, 'pledge'));
        }

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = $this->getPurposesWithChildren();
        
        $creditCards = PaymentOption::select('card_type')
                        ->where('category', 'cc')
                        ->whereNotNull('card_type')
                        ->orderBy('card_type', 'desc')
                        ->groupBy('card_type')->get();

        $cc = $creditCards->reduce(function($carry, $item) {
            $carry[array_get($item, 'card_type')] = array_get($item, 'card_type');
            return $carry;
        }, []);

        array_set($cc, 'Other', 'Other');

        $data = [
            'campaigns' => $campaigns,
            'charts' => $charts,
            'cc' => $cc,
            'split' => $pledge,
            'master' => null,
            'create_pledge' => 'true',
            'contact' => null,
            'cid' => null,
            'periods' => Constants::TIME_PERIODS,
            'purpose' => null,
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts')
        ];

        return view('pledges.create')->with($data);
    }
    
    public function calendarCalculateEndDate(Request $request) {
        
        $end = Carbon::now()->toDateString();
        $cycles = (int)array_get($request, 'billing_cycles', 1);
        
        if( $cycles == 1 ){
            $end = array_get($request, 'billing_start_date', Carbon::now()->toDateString());
            return $end;
        }
        
        $frequency = array_get($request, 'billing_period');
        $timestamp = strtotime(array_get($request, 'billing_start_date'));
        $start = Carbon::createFromTimestamp($timestamp);
        $cycles = $cycles-1; //start counts as payment in first date
        
        switch ($frequency) {
            case 'Month':
                $end = $start->addMonths($cycles);
                break;
            case 'Week':
                $end = $start->addWeeks($cycles);
                break;
            case 'Bi-Weekly':
                $cycles = $cycles * 2;
                $end = $start->addWeeks($cycles);
                break;
            default:
                $end = $start;
                break;
        }
        return $end->toDateString();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Pledges\Store $request) {
        $fields = $request->all();
        
        $recurring = (bool) array_get($request, 'is_recurring');
        if ($request->has('is_recurring') && !$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
        }
        
        if($recurring){
            $endDate = $this->calendarCalculateEndDate($request);
            array_set($fields, 'billing_end_date', $endDate);
        }

        array_set($fields, 'status', 'pledge');
        if (!$recurring) {
            array_set($fields, 'billing_start_date', array_get($fields, 'promised_pay_date'));
            array_set($fields, 'billing_end_date', array_get($fields, 'promised_pay_date'));
        }

        $result = $this->processTransactionStore($fields, true);
        
        $id = array_get($result, 'transactionTemplate.id');
        return redirect()->route('pledges.edit', ['id' => $id])->with('message', __('Pledge added succesfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $template = TransactionTemplate::findOrFail($id);
        
        $transactions = $template->pledgedTransactions()->whereIn('status', ['complete'])->orderBy('id', 'desc');
        $sum = TransactionSplit::whereIn('transaction_id', array_pluck($transactions->get(), 'id'))->sum('amount');
        
        $transaction_permissions = array_get(auth()->user()->ability([],[
            'transaction-create',
            'transaction-view',
            'transaction-update',
            'transaction-delete', 
        ],['return_type'=>'array']),'permissions');
        
        $data = [
            'template' => $template,
            'transactions' => $transactions->paginate(),
            'sum' => $sum,
            'periods' => Constants::TIME_PERIODS,
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts'),
            'transaction_permissions' => $transaction_permissions,
        ];

        return view('pledges.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request) {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $template = TransactionTemplate::findOrFail($id);
        $split = array_get($template, 'splits.0');
        
        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = $this->getPurposesWithChildren();
        $contact = "";
        $cid = null;
        if (array_get($split, 'template.contact')) {
            $contact = array_get($split, 'template.contact.first_name') . ' '
                    . array_get($split, 'template.contact.last_name')
                    . '(' . array_get($split, 'template.contact.email_1') . ')';
            $cid = array_get($split, 'template.contact.id');
        }

        $creditCards = PaymentOption::select('card_type')
                        ->where('category', 'cc')
                        ->whereNotNull('card_type')
                        ->orderBy('card_type', 'desc')
                        ->groupBy('card_type')->get();

        $cc = $creditCards->reduce(function($carry, $item) {
            $carry[array_get($item, 'card_type')] = array_get($item, 'card_type');
            return $carry;
        }, []);

        array_set($cc, 'Other', 'Other');

        $data = [
            'split' => $split,
            'campaigns' => $campaigns,
            'charts' => $charts,
            'contact' => $contact,
            'cid' => $cid,
            'action' => array_get($request, 'action'),
            'cc' => $cc,
            'master' => null,
            'create_pledge' => 'true',
            'periods' => Constants::TIME_PERIODS,
            'purpose' => array_get($split, 'purpose_id'),
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts')
        ];
        //dd($data);
        return view('pledges.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Pledges\Update $request, $id) {
        $transactionSplit = TransactionTemplateSplit::findOrFail($id);
        $fields = $request->all();

        $recurring = (bool) array_get($request, 'is_recurring', false);
        
        if (!$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
            array_set($fields, 'billing_start_date', array_get($fields, 'promised_pay_date'));
            array_set($fields, 'billing_end_date', array_get($fields, 'promised_pay_date'));
        }
        
        if ($recurring) {
            $end = $this->calendarCalculateEndDate($request);
            
            array_set($fields, 'billing_end_date', $end);
        }
        
        $result = $this->processTransactionUpdate($transactionSplit, $fields, true);
        return redirect()->route('pledges.edit', ['id' => array_get($transactionSplit, 'template.id')])->with('message', __('Pledge updated succesfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        if (!auth()->user()->can('pledge-view')) abort(402);
        
        $pledge = TransactionTemplate::findOrFail($id);
        /*
        foreach ($pledge->pledgedTransactions as $transaction) {

            foreach ($transaction->splits as $split) {
                $split->delete();
            }
            $transaction->delete();
        }
        $pledge->pledgedTransactions()->sync([]);
         * 
         */
        $pledge->delete();
        return redirect()->route('pledges.index')->with('message', __('Transaction successfully deleted'));
    }

    public function search($search, Request $request) {
        if (!auth()->user()->can('pledge-view')) abort(402);

        $transactions = TransactionSplit::join('purposes', 'transaction_splits.purpose_id', '=', 'purposes.id')
                ->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                ->join('transaction_templates', 'transaction_templates.id', '=', 'transactions.transaction_template_id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                //->join('payment_options', 'payment_options.id', '=', 'transactions.payment_option_id')
                ->select('transaction_splits.*', 'contacts.first_name', 'contacts.email_1')
                ->where(function($query) use ($request) {
                    $query->where('contacts.first_name', 'like', '%' . array_get($request, 'keyword') . '%')
                    ->orWhere('contacts.last_name', 'like', '%' . array_get($request, 'keyword') . '%');
                })
                ->where('transaction_splits.tenant_id', auth()->user()->tenant_id)
                ->where('transaction_templates.is_pledge', true);

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

        $charts = collect(Purpose::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, [0 => 'None']);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $sort = null;
        $order = 'asc';
        $nextOrder = 'asc';
        $total = $transactions->get();
        $data = [
            'pledges' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count(),
            'search' => $search,
            'charts' => $charts,
            'campaigns' => $campaigns
        ];

        return view('pledges.index')->with($data);
    }

    public function cancel($id, Request $request) {
        $pledge = TransactionTemplate::withoutGlobalScopes()->where([
                    ['id', '=', $id],
                    ['is_pledge', '=', true],
                    ['status', '!=', 'canceled'],
                ])->first();

        $split = TransactionTemplateSplit::withoutGlobalScopes()
                ->where('transaction_template_id', array_get($pledge, 'id'))
                ->first();

        if (is_null($pledge) || is_null($split)) {
            abort(404);
        }

        $contact = Contact::withoutGlobalScopes()->where('id', array_get($pledge, 'contact_id'))->first();
        $chart = Purpose::withoutGlobalScopes()->where('id', array_get($split, 'purpose_id'))->first();

        $data = [
            'pledge' => $pledge,
            'contact' => $contact,
            'chart' => $chart
        ];

        return view('emails.cancel-pledge')->with($data);
    }

    public function cancelPledge($id, Request $request) {
        $pledge = TransactionTemplate::withoutGlobalScopes()->where([
                    ['id', '=', $id],
                    ['is_pledge', '=', true],
                    ['status', '!=', 'canceled'],
                ])->first();

        $split = TransactionTemplateSplit::withoutGlobalScopes()
                ->where('transaction_template_id', array_get($pledge, 'id'))
                ->first();

        if (is_null($pledge) || is_null($split)) {
            abort(404);
        }

        $contact = Contact::withoutGlobalScopes()->where('id', array_get($pledge, 'contact_id'))->first();
        $chart = Purpose::withoutGlobalScopes()->where('id', array_get($split, 'purpose_id'))->first();
        array_set($pledge, 'status', 'canceled');
        $pledge->update();

        $content = view('emails.send.pledge-canceled-notification', ['contact' => $contact, 'chart' => $chart])->render();
        $user = User::withoutGlobalScopes()->whereHas('roles', function($query) {
                    $query->where('name', 'organization-owner');
                })->first();

        $contact = Contact::withoutGlobalScopes()->where([
                    ['user_id', '=', array_get($user, 'id')],
                    ['tenant_id', '=', array_get($user, 'tenant_id')]
                ])->first();

        $args = ['content' => $content, 'contact' => $contact, 'pledge' => $pledge];

        $notification = new PledgeEmailNotification();
        $notification->run('canceled', $args);
        unset($notification);

        return redirect()->route('pledges.canceled', ['id' => $id]);
    }

    public function canceled($id) {

        $data = [];
        return view('emails.canceled-pledge')->with($data);
    }

    public function stats(Request $request) {
        if (!auth()->user()->can('pledge-view')) abort(402);

        $splits = TransactionTemplateSplit::whereHas('template', function($query) {
                    $query->where('is_pledge', true);
                })
                ->join('purposes', 'purposes.id', '=', 'transaction_template_splits.purpose_id')
                ->join('campaigns', 'campaigns.id', '=', 'transaction_template_splits.campaign_id')
                ->groupBy('purposes.name')
                ->get(['campaigns.name as campaign', 'purposes.name as chart_of_account', DB::raw('sum(transaction_template_splits.amount) as total')]);
                
        $labels = PieChart::serialize($splits, 'chart_of_account');
        $serie = PieChart::serialize($splits, 'total');
        
        $colors = [];
        for($i = 0; $i < count($labels); $i++){
            $color = array_get(Constants::CHARTS, 'COLORS.TRANSACTION_PATH.'.$i);
            array_push($colors, $color);
        }
        
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => 'Total Pledges Amount by Purpose'];
        
        $data = [
            'chart' => $chart
        ];
        
        return view('pledges.stats')->with($data);
    }

}
