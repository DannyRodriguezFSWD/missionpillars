<?php

namespace App\Http\Controllers;

use App\Classes\Shared\Transactions\SharedTransactions;
use App\Constants;
use App\Http\Requests\Transactions;
use App\Models\Address;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\PaymentOption;
use App\Models\Purpose;
use App\Models\StatementTemplate;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use App\Models\TransactionTemplate;
use App\Models\TransactionTemplateSplit;
use App\Traits\Transactions\Transactions as TransactionsTrait;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

class TransactionSplitsController extends Controller {
    use TransactionsTrait;
    use \App\Traits\GetsPurposesWithChildren;

    const PERMISSION = 'crm-transactions';

    private $importColumns = [
        'contact_email1', 'contact_first_name', 'contact_last_name', 'contact_cell_phone', 'address_mailing_address_1', 'address_city', 'address_region',
        'address_country', 'address_postal_code', 'transaction_initiated_at', 'amount', 'purpose', 'campaign', 'comment', 'payment_category', 'payment_check_number',
        'payment_cc_last_four', 'payment_ach_last_four'
    ];
    
    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        if (!(auth()->user()->can('transaction-view') || (auth()->user()->can('transaction-self') && array_get($request, 'contact_id', 0) == auth()->user()->contact->id))) {
            abort(403);
        }

        if ($request->sort){
            $request->merge([
                'sort' => explode('|',$request->sort)[0],
                'order' => explode('|',$request->sort)[1],
            ]);
        }else{
            $request->merge([
                'sort' => 'id',
                'order' => 'desc',
            ]);
        }

        if(array_get($request, 'contact_id', 0) > 0){
            $contact = Contact::find(array_get($request, 'contact_id'));
            $transactions = SharedTransactions::search($request, $contact);
        }
        else{
            $transactions = SharedTransactions::search($request);
        }
        $transactions->with('transaction.paymentOption');

        $charts = $this->getPurposesGrouped();
        $chartsActive = $this->getPurposesGrouped(true);

        $campaigns = Campaign::where('id', '<>', 1)->orderBy('name')->get()->toArray();
        array_unshift($campaigns, ['id' => 1, 'name' => 'None']);

        if(array_get($request, 'master_id', 0) > 0){
            $transactions->join('pledge_transactions', 'pledge_transactions.transaction_id', '=', 'transactions.id')
                ->where('pledge_transactions.transaction_template_id', array_get($request, 'master_id', 0));
        }

        $total_transactions = (clone $transactions);
        $total_completed = (clone $transactions);
        
        $organization_purpose = Purpose::where('sub_type', 'organizations')
        ->orWhere('name', array_get(auth()->user(), 'tenant.organization'))->first();

        $permissions = array_get(auth()->user()->ability([],[
            'transaction-create',
            'transaction-view',
            'transaction-update',
            'transaction-delete',
        ],['return_type'=>'array']),'permissions');
        $transactions->with(['purpose' => function ($query) {
            $query->withTrashed();
        }]);
        $transactions->with(['campaign' => function ($query) {
            $query->withTrashed();
        }]);
        $transactions->with(['transaction' => function ($query) {
            $query->withTrashed();
        }]);
        $transactions->with('registry:register_id');
        $transactions->with('transaction.softCredits.splits');
        $transactions->with('transaction.softCredits.contact');
        $transactions->with('transaction.documents');
        $transactions->with('transaction.splits.tags');
        $transactions->with(['transaction.splits.purpose' => function ($query) {
            $query->withTrashed();
        }]);
        $transactions->with(['transaction.splits.campaign' => function ($query) {
            $query->withTrashed();
        }]);
        
        $tags = \App\Models\Tag::with('folder')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'folder' => ['id' => $tag->folder_id, 'name' => $tag->folder->name ?: null],
            ];
        })->sortBy('folder')->values();
        
        $data = [
            'organization_purpose' => $organization_purpose ?: [],
            'total' => $total_transactions->whereNull('parent_transaction_id')->count(),
            'sum' => $total_transactions->whereNull('parent_transaction_id')->sum('transaction_splits.amount'),
            'total_completed' => $total_completed->whereNull('parent_transaction_id')->completed()->count(),
            'sum_completed' => $total_completed->whereNull('parent_transaction_id')->completed()->sum('transaction_splits.amount'),
            'charts' => $charts,
            'chartsActive' => $chartsActive,
            'campaigns' => $campaigns,
            'export_params' => ['export' => 'all'],
            'class' => 'info',
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts'),
            'permissions' => $permissions,
            'splits' => $transactions->paginate(),
            'tags' => $tags
        ];
        if($request->ajax()){
            return response()->json($data);
        }
        return view('transactions.splits.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * NOTE This does not appear to be needed as create (and edit) is handled in a vue component in the index view
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('transaction-create')) abort(403);
        request()->session()->flash('doCreate','1');
        return redirect()->route('transactions.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Transactions\Store $request) {
        $fields = $request->all();
        $recurring = (bool) array_get($request, 'is_recurring', false);
        if (!$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
        }

        if (array_get($fields, 'check_number')) {
            array_set($fields, 'check_number', preg_replace("/[^0-9]/", '', array_get($fields, 'check_number')));
        }
        
        if ($recurring) {
            $end = $this->calendarCalculateEndDate($request);
            array_set($fields, 'billing_end_date', $end);
        }

        $result = $this->processTransactionStore($fields);

//        if (array_has($result, 'transactionSplit')) {
//            $transaction = array_get($result, 'transactionSplit');
//            array_set($transaction, 'transaction_template_split_id', array_get($result, 'transactionTemplateSplit.id'));
//            $transaction->update();
//            $id = array_get($result, 'transactionSplit.id');
//        }

        if($request->ajax()){
            if (array_get($result,'status') == 'pledge-completed') {
                return response()->json($result);
            }
            return response()->json(['status' => 'ok']);
        }

        if( array_has($fields, 'pledge_id') ){
            $message = array_has($result, 'status') ? __('Pledge its already completed') : __('Transaction added succesfully');
            return redirect()->route('pledges.show', ['id' => array_get($fields, 'pledge_id')])->with('message', $message);
        }
        else{
            return redirect()->route('transactions.edit', ['id' => $id])->with('message', __('Transaction added succesfully'));
        }

        return redirect()->route('transactions.index', ['id' => $id])->with('error', __('Something went wrong saving transaction'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {
        if (!auth()->user()->can('transaction-view')) abort(403);

        $split = TransactionSplit::findOrFail($id);
        $data = [
            'split' => $split,
            'action' => array_get($request, 'action'),
            'periods' => Constants::TIME_PERIODS
        ];
        return view('transactions.splits.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request) {
        if (!auth()->user()->can('transaction-update')) abort(403);

        $pledge = null;
        $split = TransactionSplit::findOrFail($id);

        $campaigns = collect(Campaign::all())->reduce(function($carry, $item) {
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = $this->getPurposesWithChildren();

        $contact = "";
        $cid = null;

        if (array_get($split, 'transaction.contact')) {
            $contact = array_get($split, 'transaction.contact.first_name') . ' '
                    . array_get($split, 'transaction.contact.last_name')
                    . '(' . array_get($split, 'transaction.contact.email_1') . ')';
            $cid = array_get($split, 'transaction.contact.id');
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
            'master' => null,
            'split' => $split,
            'campaigns' => $campaigns,
            'charts' => $charts,
            'contact' => $contact,
            'cid' => $cid,
            'action' => array_get($request, 'action'),
            'cc' => $cc,
            'pledge' => $pledge,
            'create_pledge' => 'false',
            'periods' => Constants::TIME_PERIODS,
            'update_recurring' => array_get($request, 'ur'),
            'purpose' => Purpose::first(),
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts')
        ];

        return view('transactions.splits.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Transactions\UpdateTransactionSplit $request) {
        $transactionSplit = TransactionSplit::findOrFail($id);
        $fields = $request->all();
        $category = array_get($fields, 'category');
        if(in_array($category, ['ach']) && array_get($request, 'payment_option_id') === '0'){
            $validator = validator()->make($request->all(), [
                'first_four' => 'required_if:payment_option_id, ==, 0',
                'last_four' => 'required_if:payment_option_id, ==, 0'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }
        }
        else if ($category === 'cc') {
            $validator = validator()->make($request->all(), [
                'card_type' => 'required_if:payment_option_id, ==, 0',
                'first_four' => 'required_if:payment_option_id, ==, 0',
                'last_four' => 'required_if:payment_option_id, ==, 0'
            ], [
                'first_four.required_if' => __('Enter a valid payment option'),
                'last_four.required_if' => __('Enter a valid payment option')
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }
        }

        $recurring = (bool) array_get($request, 'is_recurring', false);
        if (!$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
        }
        if ($recurring) {
            $end = $this->calendarCalculateEndDate($request);
            array_set($fields, 'billing_end_date', $end);
        }

        if (array_get($fields, 'check_number')) {
            array_set($fields, 'check_number', preg_replace("/[^0-9]/", '', array_get($fields, 'check_number')));
        }
        
        $result = $this->processTransactionUpdate($transactionSplit, $fields);
        
        $softCredits = array_get($transactionSplit, 'transaction.softCredits');
        
        // TODO fix softcredits
//        if (count($softCredits) > 0) {
//            foreach ($softCredits as $softCredit) {
//                array_set($softCredit, 'transaction_initiated_at', array_get($fields, 'transaction_initiated_at'));
//                array_set($softCredit, 'channel', array_get($fields, 'channel'));
//                array_set($softCredit, 'deposit_date', array_get($fields, 'deposit_date'));
//                array_set($softCredit, 'type', array_get($fields, 'type'));
//                $softCredit->update();
//                
//                foreach ($softCredit->splits as $softCreditSplit) {
//                    array_set($softCreditSplit, 'purpose_id', array_get($fields, 'purpose_id'));
//                    array_set($softCreditSplit, 'campaign_id', array_get($fields, 'campaign_id'));
//                    $softCreditSplit->update();
//                }
//            }
//        }
        
        if($request->ajax()){
            return response()->json(['status' => 'ok']);
        }
        return redirect()->route('transactions.edit', ['id' => $id, 'action' => array_get($request, 'action')])->with('message', __('Transaction updated succesfully'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateOld($id, Transactions\UpdateTransactionSplit $request) {
        $transactionSplit = TransactionSplit::findOrFail($id);
        $fields = $request->all();
        
        $category = array_get($fields, 'category');
        if(in_array($category, ['ach']) && array_get($request, 'payment_option_id') === '0'){
            $validator = validator()->make($request->all(), [
                'first_four' => 'required_if:payment_option_id, ==, 0',
                'last_four' => 'required_if:payment_option_id, ==, 0'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }
        }
        else if ($category === 'cc') {
            $validator = validator()->make($request->all(), [
                'card_type' => 'required_if:payment_option_id, ==, 0',
                'first_four' => 'required_if:payment_option_id, ==, 0',
                'last_four' => 'required_if:payment_option_id, ==, 0'
            ], [
                'first_four.required_if' => __('Enter a valid payment option'),
                'last_four.required_if' => __('Enter a valid payment option')
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }
        }

        $recurring = (bool) array_get($request, 'is_recurring', false);
        if (!$recurring) {
            $fields = $request->except(['billing_cycles', 'billing_frequency', 'billing_period', 'billing_start_date', 'billing_end_date']);
        }
        if ($recurring) {
            $end = $this->calendarCalculateEndDate($request);
            array_set($fields, 'billing_end_date', $end);
        }

        if (array_get($fields, 'check_number')) {
            array_set($fields, 'check_number', preg_replace("/[^0-9]/", '', array_get($fields, 'check_number')));
        }
        
        $result = $this->processTransactionUpdate($transactionSplit, $fields);
        
        $softCredits = array_get($transactionSplit, 'transaction.softCredits');
        
        if (count($softCredits) > 0) {
            foreach ($softCredits as $softCredit) {
                array_set($softCredit, 'transaction_initiated_at', array_get($fields, 'transaction_initiated_at'));
                array_set($softCredit, 'channel', array_get($fields, 'channel'));
                array_set($softCredit, 'deposit_date', array_get($fields, 'deposit_date'));
                array_set($softCredit, 'type', array_get($fields, 'type'));
                $softCredit->update();
                
                foreach ($softCredit->splits as $softCreditSplit) {
                    array_set($softCreditSplit, 'purpose_id', array_get($fields, 'purpose_id'));
                    array_set($softCreditSplit, 'campaign_id', array_get($fields, 'campaign_id'));
                    $softCreditSplit->update();
                }
            }
        }
        
        if($request->ajax()){
            return response()->json(['status' => 'ok']);
        }
        return redirect()->route('transactions.edit', ['id' => $id, 'action' => array_get($request, 'action')])->with('message', __('Transaction updated succesfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        if (!auth()->user()->can('transaction-delete')) abort(403);

        $split = TransactionSplit::findOrFail($id);
        if (!empty($split->transaction->system_created_by) && array_get($split, 'transaction.system_created_by') === 'Continue to Give') {
            return response(['message' => "Cannot delete giving app transaction", 'type' => 'api'], 400);
        }
        if(count($split->registry)) return response(['message' => "Sorry, you cannot delete this transaction until you unlink it from the accounting transaction you created.", 'type' => 'linked'],400);
        
        $softCredits = Transaction::where('parent_transaction_id', array_get($split, 'transaction.id'))->get();
        if ($softCredits) {
            foreach ($softCredits as $softCredit) {
                $this->deleteSoftCredit(['soft_credit_id' => array_get($softCredit, 'id')]);
            }
        }
        
        foreach ($split->transaction->template->splits as $s) {
            $s->delete();
        }
        $split->transaction->template->delete();
        $split->transaction->delete();
        
        foreach ($split->transaction->splits as $s) {
            $s->delete();
        }
        
        if ($request->ajax()) return response('success',200);
        return redirect()->route('transactions.index')->with('message', __('Transaction successfully deleted'));
    }

    public function calendarCalculateEndDate(Request $request) {

        $end = Carbon::now()->toDateString();
        $cycles = (int)array_get($request, 'billing_cycles', 1);
        if( $cycles === 1 ){
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
            case 'Two Weeks':
                $cycles = $cycles * 2;
                $end = $start->addWeeks($cycles);
                break;
            default:
                $end = $start;
                break;
        }
        return $end->toDateString();
    }

    public function export($random, Request $request) {
        if (!auth()->user()->can('transaction-view')) abort(403);

        $params = json_decode(array_get($request, 'export_params', '[]'), true);
        if(array_get($params, 'contact_id', 0) > 0){
            $contact = Contact::find(array_get($params, 'contact_id'));

            $transactions = SharedTransactions::search($params, $contact)->with('transaction.template')->get();
        }
        else{
            if(array_get($params, 'export') == 'all' ){
                $transactions = TransactionSplit::whereHas('transaction', function($query){
                    $query->whereNotIn('status', ['stub']);
                })->with('transaction.template')->get();
            }
            else{
                $transactions = SharedTransactions::search($params)->with('transaction.template')->get();
            }
        }

        $tail = str_replace(':', '', displayLocalDateTime(Carbon::now()->toDateTimeString())->toDateTimeString());
        $tail = str_replace('-', '', $tail);
        $tail = str_replace(' ', '-', $tail);
        $filename = substr(implode('-', ['transactions', $tail]), 0, 28);
        $data = [
            'transactions' => $transactions,
            'filename' => $filename
        ];

        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('transactions.splits.excel', $data);
                $sheet->setColumnFormat(array(
                    'B' => '0.00',
                ));
            });
        })->download('xlsx');

    }

    public function accountingLinking(Request $request){
        $from = array_get($request, 'from');
        $to = array_get($request, 'to');

        try{
            $from = Carbon::parse($from);
            $to = Carbon::parse($to);
        }
        catch(\Exception $e){
            $from = Carbon::now();
            $to = Carbon::now();
        }

        $transactions = TransactionSplit::doesntHave('registry')->whereHas('transaction', function($q) use($from, $to){
            $q->where('status', 'complete')->whereBetween('transaction_initiated_at', [
                $from->startOfDay(), $to->endOfDay()
            ])->whereNull('parent_transaction_id');
        })->with(['transaction', 'transaction.contact', 'transaction.template',
        'purpose' => function ($query) {
            $query->withTrashed();
        }, 'purpose.account', 'purpose.fund',
        'transaction.paymentOption' => function($query) {
            $query->withTrashed();
        }])->get();

        $result = collect($transactions)->reduce(function($carry, $item){

            $fee = 0;
            
            // we only need fees for Stripe and Wepay because they withold fees from deposits
            if ($item->transaction->payment_processor === 'stripe' || $item->transaction->payment_processor === 'wepay') {
                $fee = round(array_get($item, 'transaction.fee') * array_get($item, 'amount') / array_get($item, 'transaction.template.amount'), 2);
            }
            
            $prototype = [
                'id' => array_get($item, 'id'),
                'amount' => array_get($item, 'amount'),
                'date' => displayLocalDateTime(array_get($item, 'transaction.transaction_initiated_at'))->toDateTimeString(),
                'contact' => [
                    'id' => array_get($item, 'transaction.contact.id'),
                    'name' => implode(' ', [
                        array_get($item, 'transaction.contact.first_name'),
                        array_get($item, 'transaction.contact.last_name'),
                    ]),
                    'email' => array_get($item, 'transaction.contact.email_1'),
                ],
                'account' => [
                    'id' => array_get($item, 'purpose.account.id'),
                    'name' => array_get($item, 'purpose.account.name')
                ],
                'fund' => [
                    'id' => array_get($item, 'purpose.fund.id'),
                    'name' => array_get($item, 'purpose.fund.name')
                ],
                'transaction' => [
                    'comment' => $item->transaction->comment,
                    'channel' => $item->transaction->channel,
                    'payment_type' => $item->transaction->paymentOption ? $item->transaction->paymentOption->category : '',
                    'fee' => $fee,
                    'payment_processor' => $item->transaction->payment_processor
                ],
                'purpose' => [
                    'id'=> array_get($item, 'purpose.id'),
                    'tenant_id' => array_get($item, 'purpose.tenant_id'),
                    'name'=> array_get($item, 'purpose.name'),
                ],
            ];

            array_push($carry, $prototype);
            return $carry;
        }, []);

        return response()->json($result);
    }


    /** utility methods **/

    private function sort($sort) {
        switch ($sort) {
            case 'status':
                $field = DB::raw("CAST(transactions.status AS CHAR)");
                break;
            case 'type':
                $field = DB::raw("CAST(transaction_splits.type AS CHAR)");
                break;
            case 'date':
                $field = 'transactions.transaction_last_updated_at';
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
            default :
                $field = 'transaction_splits.amount';
                break;
        }
        return $field;
    }

    public function myTransactions(Request $request)
    {
        if (!auth()->user()->can('transaction-self')) {
            abort(403);
        }
        
        $contact = auth()->user()->contact;

        $order = array_get($request, 'order', 'desc');
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        $sort = array_get($request, 'sort', 'id');

        $statement = null;
        if (array_has($request, 'st')) {
            $statement = \App\Models\StatementTracking::findOrFail(array_get($request, 'st', 0));
        }

        $transactions = SharedTransactions::all($sort, $order, $contact, $statement);

        $total = $transactions->get();

        $templates = StatementTemplate::all();
        
        // TODO Consider adjusting permissions if the current contact is auth()->user()->contact
        $transaction_permissions = array_get(auth()->user()->ability([],[
            'transaction-create',
            'transaction-view',
            'transaction-update',
            'transaction-delete', 
        ],['return_type'=>'array']),'permissions');
        
        $data = [
            'contact' => $contact,
            'splits' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count(),
            'uuid' => Uuid::uuid4(),
            'start' => Carbon::now()->startOfYear()->toDateString(),
            'end' => Carbon::now()->endOfDay()->toDateString(),
            'templates' => $templates,
            'print_for' => ['contact' => 'Contact'],
            'statement' => null,
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts'),
            'transaction_permissions' => $transaction_permissions,
        ];
        
        return view('people.contacts.transactions')->with($data);
    }
    
    /**
     * Not fully implemented
     */
    public function importTransactions(Request $request)
    {
        $transactions = json_decode(array_get($request, 'transactions'), true);
        $dateFormat = array_get($request, 'date_format');
        $matchContactEmail = array_get($request, 'match_contact_email', true);
        $matchContactName = array_get($request, 'match_contact_name', true);
        $createNewContact = array_get($request, 'create_new_contact', true);
        $contactUpdate = array_get($request, 'contact_update', 'none');
        $addressUpdate = array_get($request, 'address_update', 'none');
        $addressMarkPrimary = array_get($request, 'address_mark_primary', false);
        $countImported = 0;
        
        foreach ($transactions as $transactionData) {
            $contact = null;
            $isNewContact = false;
            
            if ($matchContactEmail && array_get($transactionData, 'contact_email1')) {
                $contact = Contact::where('email_1', array_get($transactionData, 'contact_email1'))->first();
            }
            
            if (!$contact && $matchContactName && (array_get($transactionData, 'contact_first_name') || array_get($transactionData, 'contact_last_name'))) {
                $contactName = array_get($transactionData, 'contact_first_name').' '.array_get($transactionData, 'contact_last_name');
                $contact = Contact::whereRaw("concat(first_name, ' ', last_name) = '$contactName'")->first();
            }
            
            if (!$contact && $createNewContact) {
                $contact = new Contact();
                $isNewContact = true;
            }
            
            if (!$contact) {
                continue;
            }
            
            $contactData = [
                'first_name' => array_get($transactionData, 'contact_first_name'),
                'last_name' => array_get($transactionData, 'contact_last_name'),
                'email_1' => array_get($transactionData, 'contact_email1'),
                'cell_phone' => array_get($transactionData, 'contact_cell_phone'),
            ];
            
            if ($contactUpdate === 'all') {
                $contact = mapModel($contact, $contactData);
                
                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }
            } elseif ($contactUpdate === 'missing') {
                $contact = mapModelIfEmpty($contact, $contactData);
                
                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }
            } elseif ($contactUpdate === 'none' && $isNewContact) {
                $contact = mapModel($contact, $contactData);
                
                if (!auth()->user()->tenant->contacts()->save($contact)) {
                    abort(500);
                }
            }
            
            $contact->refresh();
            
            if (array_get($transactionData, 'address_mailing_address_1') || array_get($transactionData, 'address_city') || array_get($transactionData, 'address_region')) {
                $addressData = [
                    'mailing_address_1' => array_get($transactionData, 'address_mailing_address_1'),
                    'city' => array_get($transactionData, 'address_city'),
                    'region' => array_get($transactionData, 'address_region'),
                    'postal_code' => array_get($transactionData, 'address_postal_code'),
                    'country' => 'US',
                    'is_mailing' => $addressMarkPrimary ? 1 : 0,
                    'tenant_id' => array_get(auth()->user(), 'tenant.id'),
                    'relation_id' => array_get($contact, 'id'),
                    'relation_type' => Contact::class
                ];
                
                if ($addressUpdate === 'update') {
                    $address = $contact->addresses()->first();
                    
                    if ($address) {
                        $address = mapModel($address, $addressData);
                        $address->save();
                    }
                } elseif ($addressUpdate === 'create') {
                    $address = mapModel(new Address(), $addressData);
                    $address->save();
                } elseif ($addressUpdate === 'none') {
                    $address = $contact->addresses()->where('mailing_address_1', array_get($transactionData, 'address_mailing_address_1'))->first();
                    
                    if (!$address) {
                        $address = mapModel(new Address(), $addressData);
                        $address->save();
                    }
                }
            }
            
            $transactionTime = Carbon::createFromFormat($dateFormat, array_get($transactionData, 'transaction_initiated_at'))->toDateString().' 12:00:00';
                    
            if (!is_null($transactionTime)) {
                // TODO - Find a better way to handle localization according to tenant local time
                $transactionTime = setUTCDateTime($transactionTime, 'America/Chicago');
            }

            $campaign = Campaign::where('name', array_get($transactionData, 'campaign'))->first();
            $purpose = Purpose::where('name', array_get($transactionData, 'purpose'))->first();
            if (!$purpose) {
                $purpose = Purpose::where('sub_type', 'organizations')->first();
            }
            
            $transactionTemplateData = [
                'completion_datetime' => $transactionTime,
                'amount' => array_get($transactionData, 'amount'),
                'is_recurring' => 0,
                'is_pledge' => 0,
                'successes' => 1,
                'tax_deductible' => 1,
                'contact_id' => array_get($contact, 'id')
            ];
            
            $transactionTemplate = new TransactionTemplate();
            mapModel($transactionTemplate, $transactionTemplateData);
            if (!auth()->user()->tenant->transactionTemplates()->save($transactionTemplate)) {
                abort(500);
            }
            $transactionTemplate->refresh();
            
            $newTransactionData = [
                'transaction_initiated_at' => $transactionTime,
                'transaction_last_updated_at' => $transactionTime,
                'channel' => 'unknown',
                'check_number' => array_get($transactionData, 'payment_check_number'),
                'system_created_by' => 'Import',
                'status' => 'complete',
                'transaction_path' => 'import',
                'anonymous_amount' => 'protected',
                'anonymous_identity' => 'protected',
                'type' => 'donation',
                'tax_deductible' => 1,
                'contact_id' => array_get($contact, 'id'),
                'transaction_template_id' => array_get($transactionTemplate, 'id')
            ];
            
            $transaction = new Transaction();
            mapModel($transaction, $newTransactionData);
            if (!auth()->user()->tenant->transactions()->save($transaction)) {
                abort(500);
            }
            $transaction->refresh();
            
            $transactionTemplateSplitData = [
                'campaign_id' => array_get($campaign, 'id') ? array_get($campaign, 'id') : 1,
                'purpose_id' => array_get($purpose, 'id') ? array_get($purpose, 'id') : 1,
                'tax_deductible' => 1,
                'type' => 'donation',
                'amount' => array_get($transactionData, 'amount'),
                'transaction_template_id' => array_get($transactionTemplate, 'id')
            ];
            
            $splitTemplate = new TransactionTemplateSplit();
            mapModel($splitTemplate, $transactionTemplateSplitData);
            if (!auth()->user()->tenant->transactionTemplateSplits()->save($splitTemplate)) {
                abort(500);
            }
            $splitTemplate->refresh();
            
            $transactionSplitData = [
                'campaign_id' => array_get($campaign, 'id') ? array_get($campaign, 'id') : 1,
                'purpose_id' => array_get($purpose, 'id') ? array_get($purpose, 'id') : 1,
                'amount' => array_get($transactionData, 'amount'),
                'type' => 'donation',
                'tax_deductible' => 1,
                'transaction_id' => array_get($transaction, 'id'),
                'transaction_template_split_id' => array_get($splitTemplate, 'id')
            ];
            
            $split = new TransactionSplit();
            mapModel($split, $transactionSplitData);
            if (!auth()->user()->tenant->transactionSplits()->save($split)) {
                abort(500);
            }
            $split->refresh();
            
            $countImported++;
        }
        
        return response()->json(['success' => true, 'count' => $countImported]); 
    }
    
    public function parseImport()
    {
        $validateFile = Validator::make(
            ['file' => request()->file, 'extension' => strtolower(request()->file->getClientOriginalExtension()),],
            ['file' => 'required', 'extension' => 'required|in:csv,xlsx,xls',]);

        if ($validateFile->fails()) return response('Invalid file format, only csv, xlsx, xls are allowed', 400);

        try {
            config(['excel.import.heading' => false]);
            config(['excel.import.dates.enabled' => false]);
            $transactions = Excel::load(request()->file)->get();
            return response(json_encode(['columns' => $transactions[0], 'transactions' => $transactions]));
        } catch (\Exception $exception) {
            return response('Something went wrong', 400);
        }
    }
    
    public function importPreview(Request $request)
    {
        $transactions = array_get($request, 'transactions');
        $matchContactEmail = array_get($request, 'match_contact_email');
        $matchContactName = array_get($request, 'match_contact_name');
        $createNewContact = array_get($request, 'create_new_contact');
        
        $transactions2 = [];
        $hasTransactionsToImport = false;
        $hasErrors = false;
        $transactionsWithErrors = [];
        
        if (array_get($request, 'has_header')) {
            $header = $transactions[0];
            array_push($header, 'Error');
            $transactionsWithErrors[] = $header;
        }
        
        if (array_get($request, 'has_header')) {
            array_shift($transactions);
        }
        
        foreach ($transactions as $key => $transaction) {
            $tran = [
                'id' => $key,
                'error' => null
            ];
            
            foreach ($this->importColumns as $importColumn) {
                $tran[$importColumn] = array_get($request, "columns.$importColumn") !== null ? array_get($transaction, array_get($request, "columns.$importColumn")) : null;
            }
            
            $validator = Validator::make($tran, [
                'amount' => 'numeric|required',
                'transaction_initiated_at' => 'required',
            ]);
            
            if ($validator->fails()) {
                $tran['error'] = join(', ', $validator->errors()->all());
                $tran['row_number'] = array_get($request, 'has_header') ? ($key + 2) : ($key + 1);
                $hasErrors = true;
            } else {
                $dateFormat = array_get($request, 'date_format');
                $date_arr = date_parse_from_format($dateFormat, $tran['transaction_initiated_at']);
                if (checkdate($date_arr['month'],$date_arr['day'],$date_arr['year'])) {
                    $tran['transaction_initiated_at'] = Carbon::parse(join('-',array_splice($date_arr, 0, 3)))->format($dateFormat);
                } else {
                    $formats = [
                        'Y-m-d' => 'YYYY-MM-DD or YYYY/MM/DD',
                        'm-d-Y' => 'MM-DD-YYYY or MM/DD/YYYY',
                        'd-m-Y' => 'DD-MM-YYYY or DD/MM/YYYY',
                        'Y-d-m' => 'YYYY-DD-MM or YYYY/DD/MM',
                    ];
                    
                    $tran['error'] = 'Invalid Date "<b>' . $tran['transaction_initiated_at'] . '</b>" on format ' . $formats[$dateFormat] . '.';
                    $tran['row_number'] = array_get($request, 'has_header') ? ($key + 2) : ($key + 1);
                    $hasErrors = true;
                }
                
                if (array_get($tran, 'purpose')) {
                    $purpose = Purpose::where('name', array_get($tran, 'purpose'))->first();
                    
                    if (!$purpose) {
                        $tran['error'] = 'Did not find purpose with name "<b>'.array_get($tran, 'purpose').'</b>".';
                        $tran['row_number'] = array_get($request, 'has_header') ? ($key + 2) : ($key + 1);
                        $hasErrors = true;
                    }
                }
                
                if (array_get($tran, 'campaign')) {
                    $campaign = Campaign::where('name', array_get($tran, 'campaign'))->first();
                    
                    if (!$campaign) {
                        $tran['error'] = 'Did not find fundraiser with name "<b>'.array_get($tran, 'campaign').'</b>".';
                        $tran['row_number'] = array_get($request, 'has_header') ? ($key + 2) : ($key + 1);
                        $hasErrors = true;
                    }
                }
                
                $contact = null;
            
                if ($matchContactEmail && array_get($tran, 'contact_email1')) {
                    $contact = Contact::where('email_1', array_get($tran, 'contact_email1'))->first();
                }

                if (!$contact && $matchContactName && (array_get($tran, 'contact_first_name') || array_get($tran, 'contact_last_name'))) {
                    $contactName = array_get($tran, 'contact_first_name').' '.array_get($tran, 'contact_last_name');
                    $contact = Contact::whereRaw("concat(first_name, ' ', last_name) = '$contactName'")->first();
                }

                if (!$contact && !$createNewContact) {
                    $tran['error'] = 'Unable to find this contact by email or by name and you have selected to not create new contact.';
                    $tran['row_number'] = array_get($request, 'has_header') ? ($key + 2) : ($key + 1);
                    $hasErrors = true;
                }
            }
            
            if (array_get($tran, 'error')) {
                $temp = $transaction;
                array_push($temp, array_get($tran, 'error'));
                $transactionsWithErrors[] = $temp;
            } else {
                $hasTransactionsToImport = true;
            }

            array_push($transactions2, $tran);
        }
        
        return response()->json([
            'success' => true,
            'transactions' => $transactions2,
            'hasTransactionsToImport' => $hasTransactionsToImport,
            'hasErrors' => $hasErrors,
            'transactionsWithErrors' => $transactionsWithErrors
        ]);
    }
}
