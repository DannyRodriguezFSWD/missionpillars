<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Models\Folder;
use App\Models\Purpose;
use App\Models\Tag;
use App\Http\Requests\Transactions;
use App\Constants;

class TransactionsController extends Controller
{
    const PERMISSION = 'crm-transactions';

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
            case 'status':
                $field = DB::raw("CAST(transactions.status AS CHAR)");
                break;
            case 'type':
                $field = DB::raw("CAST(transactions.type AS CHAR)");
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
            default :
                $field = 'transactions.amount';
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
        if (!auth()->user()->can('transaction-view')) abort(402);
        
        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sort($sort);

            $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                    ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join('payment_options', 'payment_options.id', '=', 'transactions.payment_option_id')
                    ->with('transactionSplits.tags')
                    ->select('transactions.*', 'payment_options.card_type','transaction_splits.ta')
                    ->where('transactions.tenant_id', auth()->user()->tenant->id)
                    ->orderBy($field, $order)
                    ->paginate();

            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        } else {
            $transactions = TransactionSplit::paginate();
            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }

        $data = ['transactions' => $transactions, 'sort' => $sort, 'order' => $order, 'nextOrder' => $nextOrder, 'total' => Transaction::all()->count()];
        return view('transactions.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('transaction-create')) abort(402);
        
        $campaigns = collect(Campaign::all())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::all())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, ['None']);

        $data = [
            'campaigns' => $campaigns,
            'charts' => $charts
        ];

        return view('transactions.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Transactions\Store $request)
    {
        $tags = array_get($request, 'tags', []);
        array_set($request, 'status', 'complete');
        $transaction = mapModel(new Transaction(), $request->all());
        array_set($transaction, 'transaction_initiated_at', date('Y-m-d H:i:s'));
        array_set($transaction, 'transaction_last_updated_at', date('Y-m-d H:i:s'));
        if(auth()->user()->tenant->transactions()->save($transaction)){
            return redirect()->route('transactions.edit', ['id' => array_get($transaction, 'id')])->with('message', __('Transaction added succesfully'));
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('transaction-view')) abort(402);
        
        $transaction = Transaction::findOrFail($id);
        $data = ['transaction' => $transaction];
        return view('transactions.show')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('transaction-update')) abort(402);
        
        $transaction = Transaction::findOrFail($id);
        $campaigns = collect(Campaign::all())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, []);

        $charts = collect(Purpose::all())->reduce(function($carry, $item){
            $carry[$item->id] = $item->name;
            return $carry;
        }, ['None']);

        $contact = "";
        if($transaction->contact){
            $contact = $transaction->contact->first_name.' '.$transaction->contact->last_name.'('.$transaction->contact->email_1.')';
        }

        $data = [
            'transaction' => $transaction,
            'campaigns' => $campaigns,
            'charts' => $charts,
            '_contact' => $contact
        ];

        return view('transactions.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Transactions\UpdateTransaction $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $c = array_get($request, 'campaign_id');
        if( !is_null($c) && $c != 'NULL' ){
            $campaign = explode('-', $c);
            if( count($campaign) > 0 ){
                array_set($request, 'campaign_id', $campaign[0]);
                array_set($transaction, 'campaign_id', array_get($request, 'campaign_id'));
            }
        }

        array_set($transaction, 'contact_id', array_get($request, 'contact_id'));
        array_set($transaction, 'amount', array_get($request, 'amount'));
        array_set($transaction, 'fee', array_get($request, 'fee'));
        if( $request->has('tax_deductible') ){
            array_set($transaction, 'tax_deductible', array_get($request, 'tax_deductible'));

        }
        array_set($transaction, 'type', array_get($request, 'type'));
        array_set($transaction, 'purpose_id', array_get($request, 'purpose_id'));

        if($transaction->update()){
            return redirect()->route('transactions.edit', ['id' => $id])->with('message', __('Transaction updated succesfully'));
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!auth()->user()->can('transaction-delete')) abort(402);
        
        Transaction::destroy($id);
        return redirect()->route('transactions.index');
    }

    private function transactionsFoundByKeyword($request) {
        $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*')
                ->where('transactions.tenant_id', auth()->user()->tenant->id)
                ->where('contacts.first_name', 'like', '%'.array_get($request, 'keyword').'%')
                ->orWhere('contacts.last_name', 'like', '%'.array_get($request, 'keyword').'%')
                ->paginate();
        return $transactions;
    }

    private function transactionsFoundByEmail($request) {
        $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*')
                ->where('transactions.tenant_id', auth()->user()->tenant->id)
                ->where('contacts.email_1', array_get($request, 'email'))
                ->paginate();
        return $transactions;
    }

    private function transactionsFoundByStatus($request) {
        $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*')
                ->where('transactions.tenant_id', auth()->user()->tenant->id)
                ->where('transactions.status', array_get($request, 'status'))
                ->paginate();
        return $transactions;
    }

    private function transactionsFoundByRange($request) {
        $min = array_get($request, 'min');
        $min .= ' 00:00:00';
        $max = array_get($request, 'max');
        $max .= ' 23:59:59';
        $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*')
                ->where('transactions.tenant_id', auth()->user()->tenant->id)
                ->whereBetween('transactions.transaction_last_updated_at', [$min, $max])
                ->paginate();
        return $transactions;
    }

    public function search($search, Request $request) {
        if (!auth()->user()->can('transaction-view')) abort(402);
        
        $transactions = Transaction::join('purposes', 'transactions.purpose_id', '=', 'purposes.id')
                ->join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*', 'contacts.first_name', 'contacts.email_1')
                ->where(function($query) use ($request){
                    $query->where('contacts.first_name', 'like', '%'.array_get($request, 'keyword').'%')
                            ->orWhere('contacts.last_name', 'like', '%'.array_get($request, 'keyword').'%');

                });

        if( is_null( array_get($request, 'email') ) == false ){
            $transactions->where('contacts.email_1', '=', array_get($request, 'email'));
        }

        $start = array_get($request, 'start');
        $end = array_get($request, 'end');

        if( $start != '' && $end != '' ){
            $start .= ' 00:00:00';
            $end .= ' 23:59:59';
            $transactions->whereBetween('transactions.transaction_initiated_at', [$start, $end]);
        }

        if( !is_null(array_get($request, 'status')) && strtolower(array_get($request, 'status')) != 'all' ){
            $transactions->where('transactions.status', array_get($request, 'status'));
        }

        $sort = null;
        $order = 'asc';
        $nextOrder = 'asc';

        $data = [
            'transactions' => $transactions->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'total' => Transaction::all()->count(),
            'search' => $search
        ];

        return view('transactions.index')->with($data);
    }

}
