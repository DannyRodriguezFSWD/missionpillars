<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purposes\UpdatePurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Purpose;
use App\Http\Requests\Purposes\StorePurpose;
use App\Models\Contact;
use App\Constants;

class PurposesController extends Controller
{
    const PERMISSION = 'crm-purposes';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(!auth()->user()->tenant->can(self::PERMISSION)){
                return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
            }
            return $next($request);
        });
    }


    private function sort($sort) {
        switch ($sort) {
            case 'type':
                $field = 'type';
                break;
            default :
                $field = 'name';
                break;
        }
        return $field;
    }

    private function sortTransactions($sort){
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
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort = 'title';
        $order = 'asc';
        $nextOrder = 'asc';
        if ($request->has('sort')) $sort = array_get($request, 'sort');
        if ($request->has('order')) $order = array_get($request, 'order');
        $field = $this->sort($sort);

        $charts = Purpose::with(['account','fund',
        'getChildren'=> function ($q) use ($field, $order){
            $q->orderBy($field, $order);
        }])
        ->whereNull('parent_purposes_id')
        ->orderBy('type', 'desc')
        ->orderBy($field, $order);
        
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';

        $data = [
            'charts' => $charts->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => Purpose::all()->count()
        ];

        return view('chart_of_accounts.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create',Purpose::class);
        $data = [
            'account' => null,
            'account_name' => null,
            'fund' => null,
            'fund_name' => null,
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts')
        ];
        return view('chart_of_accounts.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurpose $request)
    {
        $purpose = mapModel(new Purpose(), $request->except('account_id'));
        $purpose->fund_id = $request->fund_id;
        $purpose->account_id = $request->account_id;
        
        $parentPurpose = Purpose::where('sub_type', 'organizations')->first();
        $purpose->parent_purposes_id = array_get($parentPurpose, 'id');

        if( !auth()->user()->tenant->purposes()->save($purpose) ) abort(500);
        
        return redirect()->route('purposes.index')->with('message', __('Purpose successfully added'));
    }

    /**
     * Display the specified resource.
     *
     * @param Purpose $purpose
     * @return \Illuminate\Http\Response
     */
    public function show(Purpose $purpose)
    {
        $this->authorize('show',$purpose);
        $data = ['chart' => $purpose];
        return view('chart_of_accounts.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $purpose = Purpose::findOrFail($id);
        $this->authorize('update',$purpose);
        $account = array_get($purpose, 'account');
        $account_name = null;
        if(!is_null($account)){
            $account_name = array_get($account, 'number').' - '.array_get($account, 'name');
        }

        $fund = array_get($purpose, 'fund');
        $fund_name = null;
        if(!is_null($fund)){
            $fund_name = array_get($fund, 'name');
        }

        $contactId = array_get($purpose, 'contact_id');
        $autocomplete = null;
        if( !is_null($contactId) ){
            $contact = Contact::findOrFail($contactId);
            $autocomplete = array_get($contact, 'first_name').' '.array_get($contact, 'last_name').' '.'('.array_get($contact, 'email_1').')';
        }
        $data = [
            'chart' => $purpose,
            'autocomplete' => $autocomplete,
            'action' => array_get($request, 'action'),
            'account' => $account,
            'account_name' => $account_name,
            'fund' => $fund,
            'fund_name' => $fund_name,
            'from_c2g' => $purpose->createdFromC2G(),
            'link_purposes_and_accounts' => auth()->user()->tenant->can('accounting-accounts')
        ];
        return view('chart_of_accounts.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurpose $request)
    {
        $purpose = $request->purpose_;
        $this->authorize('update',$purpose);
        if ($purpose->createdFromC2G()){
            mapModel($purpose, $request->except('account_id','name','description','type'));
        }else{
            array_set($purpose, 'is_active', false);
            mapModel($purpose, $request->except('account_id'));
            
            if (array_get($purpose, 'type') === 'Missionary') {
                $purpose->parent_purposes_id = null;
            }
        }
        array_set($purpose, 'account_id', null);
        $purpose->account_id = $request->account_id;
        $purpose->fund_id = $request->fund_id;
        if(auth()->user()->tenant->purposes()->save($purpose) ){
            return redirect()->route('purposes.edit', ['id' => $purpose->id])->with('message', __('Purpose successfully updated'));
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $purpose = Purpose::findOrFail($id);
        $this->authorize('delete',$purpose);
        Purpose::destroy($id);
        return redirect()->route('purposes.index')->with('message', __('Purpose successfully deleted'));
    }

    public function transactions($id, Request $request) {
        $chart = Purpose::findOrFail($id);

        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sortTransactions($sort);

            $splits = $chart->transactions()->join('purposes', 'transaction_splits.purpose_id', '=', 'purposes.id')
                    ->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                    ->join('transaction_templates', 'transaction_templates.id', '=', 'transactions.transaction_template_id')
                    ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join('payment_options', 'payment_options.id', '=', 'transactions.payment_option_id')
                    ->select('transaction_splits.*', 'payment_options.card_type', 'transactions.transaction_last_updated_at')
                    ->where([
                        ['transaction_splits.tenant_id', '=', auth()->user()->tenant->id],
                        ['transaction_templates.is_pledge', '=', false],
                        ['transactions.status', '!=', 'stub']
                    ])
                    ->orderBy($field, $order);

            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        }
        else{
            $splits = $chart->transactions()->join('purposes', 'transaction_splits.purpose_id', '=', 'purposes.id')
                    ->join('transactions', 'transactions.id', '=', 'transaction_splits.transaction_id')
                    ->join('transaction_templates', 'transaction_templates.id', '=', 'transactions.transaction_template_id')
                    ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join('payment_options', 'payment_options.id', '=', 'transactions.payment_option_id')
                    ->select('transaction_splits.*', 'payment_options.card_type', 'transactions.transaction_last_updated_at')
                    ->where([
                        ['transaction_splits.tenant_id', '=', auth()->user()->tenant->id],
                        ['transaction_templates.is_pledge', '=', false],
                        ['transactions.status', '!=', 'stub']
                    ]);

            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }

        $total = $splits->get();

        $data = [
            'splits' => $splits->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => $total->count(),
            'chart' => $chart,
        ];

        return view('chart_of_accounts.transactions')->with($data);
    }

}
