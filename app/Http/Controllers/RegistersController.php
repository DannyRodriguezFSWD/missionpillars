<?php

namespace App\Http\Controllers;

use App\Classes\Accounting;
use App\Constants;
use App\Http\Requests;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Contact;
use App\Models\Fund;
use App\Models\Register;
use App\Models\RegisterSplit;
use App\Models\StartingBalance;
use App\Traits\AmountTrait;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class RegistersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use AmountTrait;

    const PERMISSION = 'accounting-transactions';

    public function __construct(){
        $this->middleware(function ($request, $next) {
            if(auth()->check()){
                if(!auth()->user()->tenant->can(self::PERMISSION)){
                    return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
                }
            }
            return $next($request);
        });
    }

    public function index()
    {
        if (!auth()->user()->can('accounting-view')) abort(403);
        $funds = Fund::all();
        $groups = AccountGroup::with('accounts')->orderBy('name', 'asc')->get();
        $accounts_register = Account::where('account_type','register')->get();

        foreach ($accounts_register as $acc) {
            $g = AccountGroup::where('id', $acc->account_group_id)->value('chart_of_account');
            $acc->group_type = $g;
        }
        $contacts = Contact::all();
        $registers = Register::all()->toArray();

        // this array controls whether or not the create/edit/delete buttons are displayed on the page
        $permissions = array_get(auth()->user()->ability([],[
            'accounting-create',
            'accounting-update',
            'accounting-delete', 
        ],['return_type'=>'array']),'permissions');
        
        return view('registers.index', compact('funds', 'groups', 'accounts_register', 'contacts', 'registers', 'permissions'));
    }

    public function getSplits(Request $request)
    {
        $acc_id = $request->input('register_id');
        $register = Register::with('account:id,name,number')->findOrFail($acc_id);

        $reg_id = array_get($register, 'account_register_id');

        $select_fields = [
            'spl.*',
            'acc.name as account_name',
            'acc.number as account_number',
            'f.name as fund_name',
            'spl.account_id as @acc_id'
        ];

        if(array_get($request, 'register_type') != 'fund_transfer'){
            array_push($select_fields, 'c.first_name as first_name');
            array_push($select_fields, 'c.last_name as last_name');
            array_push($select_fields, 'c.email_1 as email');
        }

        $query = DB::table('register_splits as spl')
            ->select($select_fields)
            ->join('registers as r', 'spl.register_id', '=', 'r.id')
            ->join('accounts as acc', 'spl.account_id', '=', 'acc.id')
            ->join('funds as f', 'spl.fund_id', '=', 'f.id');

            if(array_get($request, 'register_type') != 'fund_transfer'){
                $query->join('contacts as c', 'spl.contact_id', '=', 'c.id');

                $query->leftJoin('transactions_registers as tr','tr.register_split_id','=','spl.id');
                $query->leftJoin('transaction_splits as ts','tr.transaction_split_id','=','ts.id');
                $query->whereNull('ts.deleted_at');
            }
            $query->where('r.id', $acc_id);
            if(array_get($request, 'register_type') != 'fund_transfer'){
                $query->where(function ($query) use($acc_id, $reg_id) {
                    $helper = RegisterSplit::where('register_id', $acc_id)->where('account_id', $reg_id)->first();
    
                    $query->where('spl.account_id', '!=', $reg_id)
                    ->orwhere('spl.id', array_get($helper, 'splits_partner_id'));
                });
            }

        $splits = $query->get();
        $s = $this->processRegisterData($splits, true);
        $r = $this->processRegisterData($register);

        return ['splits' => $s, 'register' => $r];
    }

    public function processRegisterData($data, $splits = false)
    {
        $result = array();
        if ($splits) {
            foreach($data as $split) {
                $r['amount'] = number_format(abs($split->amount), 2, ".", "");
                $r['credit'] = $split->credit;
                $r['debit'] = $split->debit;
                $r['comment'] = $split->comment;
                $r['tag'] = $split->tag;
                $r['fund_id'] = $split->fund_id;
                $r['account_id'] = $split->account_id;
                $r['contact_id'] = $split->contact_id;
                $r['id'] = $split->id;
                $r['contact'] = array_get((array) $split, 'first_name') . ' ' . array_get((array) $split, 'last_name') . ' (' . array_get((array) $split, 'email') . ')';
                $r['fund_name'] = $split->fund_name;
                $r['account_name'] = $split->account_name;
                $result[] = $r;
            }
        } else {
            $result['id'] = $data->id;
            $result['account_register_id'] = $data->account_register_id;
            $result['account_id'] = $data->account_register_id;
            $result['amount'] = number_format(abs($data->amount), 2, ".", "");
            $result['fund_transfer_amount'] = number_format(abs($data->amount), 2, ".", "");
            $result['credit'] = $data->credit;
            $result['debit'] = $data->debit;
            $result['date'] = $data->date;
            $result['comment'] = $data->comment;
            $result['check_number'] = $data->check_number;
            $result['journal_entry_id'] = $data->journal_entry_id;
            $result['register_type'] = $data->register_type;
            if ($data->account) $result['account'] = $data->account->toArray();
        }
        return $result;
    }

    public function show($id, Request $request){
        if (!auth()->user()->can('accounting-view')) abort(403);
        switch ($id) {
            case 'table':
                $result = $this->getTableData($request);
                break;

            case 'getSplits':
                $result = $this->getSplits($request);
                break;
            
            default:
                $result = [];
                break;
        }

        return response()->json($result);
    }

    public function getTableData(Request $request)
    {
        $acc_id = $request->input('acc_id');
        $tenant_id = array_get(auth()->user(), 'tenant.id');
        $sort = ['date', 'desc'];

        if ($request->input('sort')) {
            $sort = explode('|', $request->input('sort'));
            if ($sort[0] == 'contact') {
                $sort[0] = 'first_name';
            } elseif ($sort[0] == 'check') {
                $sort[0] = 'register_check_number';
            } elseif ($sort[0] == 'journal entry #') {
                $sort[0] = 'journal_entry_id';
            }
        }
        
        $select_fields = [
            're.id',
            're.date',
            're.account_register_id',
            're.check_number as register_check_number',
            're.register_type',
            're.comment as memo',
            're.journal_entry_id as journal_entry_id',
            'searched_splits.register_id',
            'searched_splits.contact_id as se_contact',
            'searched_splits.fund_id as se_fund',
            'searched_splits.amount as se_amount',
            'searched_splits.account_id',
            'searched_splits.comment',
            'searched_splits.credit',
            'searched_splits.debit',
            'searched_splits.tag',
            'searched_splits.contact_id',
            'partner_splits.account_id as partner_account_id',
            'a.name as searched_acc_name',
            'a.number as searched_acc_num',
            'acc.name as partner_account_name',
            'acc.number as partner_account_num',
            'contacts.first_name as first_name',
            'contacts.last_name as last_name',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(searched_splits.amount) as searched_for_amount')
        ];

        $subQuery = DB::table('register_splits as searched_splits')
            ->select($select_fields)
            ->leftJoin('register_splits as partner_splits', 'searched_splits.splits_partner_id', '=', 'partner_splits.id')
            ->join('registers as re', 'searched_splits.register_id', '=', 're.id')
            ->join('accounts as a', 'searched_splits.account_id', '=', 'a.id')
            ->leftJoin('accounts as acc', 'partner_splits.account_id', '=', 'acc.id')
            ->leftJoin('contacts as contacts', 'searched_splits.contact_id', '=', 'contacts.id')
            ->where('searched_splits.tenant_id', $tenant_id);
        $subQuery = $this->appendSearchToQuery($request,$subQuery);
        $subQuery->groupBy('re.id')
            ->orderBy('re.date', 'asc')
            ->orderBy('re.id', 'asc');
        
        if($request->has('acc_id')) {
            $subQuery->where('searched_splits.account_id', $acc_id);
        }

        if($request->has('type')) {
            $type = $request->get('type');
            if(is_array($type)) {
                $subQuery->whereIn('re.register_type', $type);
            } else {
                $subQuery->where('re.register_type', $type);
            }
        }
        
        $subQ = $subQuery->toSql();
        $stargingBalance = StartingBalance::where('account_id', $acc_id)->sum('balance');

        $test = DB::table(DB::raw("($subQ) as t"))
            ->select(
                "*",
                DB::raw("(@running_total := @running_total + t.searched_for_amount) as cumulative_sum")
            )
            ->join(
                DB::raw("((SELECT @running_total := $stargingBalance) as r)"), function($join) {
                $join->on(DB::raw(1), '=', DB::raw(1));
            }
            )
            ->mergeBindings($subQuery);
        $testQ = $test->toSql();
        
        $finalQ = DB::table(DB::raw("($testQ) as t2"))
            ->select("*");
        
        if ($sort[0] === 'date') {
            $finalQ->orderBy($sort[0], $sort[1])
                ->orderBy('id', $sort[1]);
        } elseif ($sort[0] === 'contact') {
            $finalQ->orderBy($sort[0], $sort[1])
                ->orderBy('last_name', $sort[1]);
        } else {
            $finalQ->orderBy($sort[0], $sort[1]);
        }
        
        $finalQ->mergeBindings($test);
        
        $result = $finalQ->paginate(10);
        $data = $this->processData($result, $acc_id);

        return $data;
    }

    public function processData($query_results, $acc_id)
    {
        $data = $query_results;
        $itemsTransformed = $data->getCollection()->map(function ($item) use ($acc_id) {
            $contact = Contact::find($item->contact_id);
            $comment = $item->comment;
            if ($item->register_type === 'journal_entry') {
                $comment = 'Journal Entry';
            } else if ($item->register_type === 'fund_transfer') {
                $comment = 'Fund Transfer';
            }
            
            $item_data = [
                'amount' => $item->se_amount,
                'date' => $item->date,
                'tag' => $item->tag ? $item->tag : '-',
                'comment' => $comment,
                'account' => '-',
                'check' => ($item->register_check_number ? $item->register_check_number : '-'),
                'contact' => array_get($contact, 'first_name') . ' ' . array_get($contact, 'last_name'),
                'register_id' => $item->register_id,
                'balance' => $item->cumulative_sum,
                'register_type' => $item->register_type,
            ];
            
            if ($item->count > 1) { // multiple split rows exist for current register 
                $item_data ['tag'] = '-';
                
                if ($item->register_type == null) $item_data['comment'] = 'Split';
                if ($item->register_type !== 'fund_transfer') $item_data['amount'] = $item->searched_for_amount;
                $item_data = array_merge($item_data, [
                    'journal_entry_id' => $item->journal_entry_id,
                    'journal entry #' => $item->journal_entry_id,
                    'account_id' => $item->account_register_id,
                    'fund_transfer_amount' => abs($item->se_amount),
                    'memo' => $item->memo,
                ]);
            } elseif ($item->register_type !== 'journal_entry') {
                $account = ($item->account_register_id === $acc_id 
                    ? ($item->searched_acc_num . ' - ' . $item->searched_acc_name) 
                    : ($item->partner_account_num . ' - ' . $item->partner_account_name));
                $item_data['account'] = $account;
            }
            
            return $item_data;
        })->toArray();
        
        $itemsTransformedAndPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsTransformed,
            $data->total(),
            $data->perPage(),
            $data->currentPage(),
            [
                'path' => \Request::url(),
                'query' => []
            ]
        );
        
        return $itemsTransformedAndPaginated;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\Accounting\Store $request)
    {
        $registry_record_type = null;
        $splits_records_type = null;
        $registry = array_get($request, 'register');
        if(!empty(array_get($registry, 'credit'))){
            $registry_record_type = 'credit';
            $splits_records_type = 'debit';
        }
        else if(!empty(array_get($registry, 'debit'))){
            $registry_record_type = 'debit';
            $splits_records_type = 'credit';
        }
        
        $request_splits = array_get($request, 'splits', []);
        if(array_get($registry, 'register_type') == 'fund_transfer'){
            //we make cedit and debit splits from the register
            $request_splits = [
                [
                    'fund_id' => array_get($registry, 'source_fund_id'),
                    'account_id' => array_get($registry, 'account_id'),
                    'amount' => array_get($registry, 'amount'),
                    'credit' => array_get($registry, 'amount'),
                ],
                [
                    'fund_id' => array_get($registry, 'target_fund_id'),
                    'account_id' => array_get($registry, 'account_id'),
                    'amount' => array_get($registry, 'amount'),
                    'debit' => array_get($registry, 'amount'),
                ]
            ];
        }
        $register = Accounting::createOrUpdateRegistry($registry, $registry_record_type);
        $splits = Accounting::createOrUpdateSplits($register, $request_splits, $registry_record_type, $splits_records_type);
        

        return response()->json(['success' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\Accounting\Update $request){

        $registry_record_type = null;
        $splits_records_type = null;
        $registry = array_get($request, 'register');
        $request_splits = array_get($request, 'splits', []);

        if(!empty(array_get($registry, 'credit'))){
            $registry_record_type = 'credit';
            $splits_records_type = 'debit';
        }
        else if(!empty(array_get($registry, 'debit'))){
            $registry_record_type = 'debit';
            $splits_records_type = 'credit';
        }

        if(array_get($request, 'type') == 'fund_transfer'){
            array_set($registry, 'amount', array_get($request, 'register.fund_transfer_amount', 0));
            if(!empty(array_get($registry, 'credit'))){
                array_set($registry, 'credit', array_get($request, 'register.fund_transfer_amount', 0));
            }

            if(!empty(array_get($registry, 'debit'))){
                array_set($registry, 'debit', array_get($request, 'register.fund_transfer_amount', 0));
            }

            foreach(array_get($request, 'splits', []) as $key => $split){
                array_set($request_splits, "$key.amount", array_get($request, 'register.fund_transfer_amount', 0));
                array_set($request_splits, "$key.account_id", array_get($registry, 'account_register_id', 0));

                if(!empty(array_get($split, 'credit'))){
                    array_set($request_splits, "$key.credit", array_get($request, 'register.fund_transfer_amount', 0));
                }
    
                if(!empty(array_get($split, 'debit'))){
                    array_set($request_splits, "$key.debit", array_get($request, 'register.fund_transfer_amount', 0));
                }
            }
        }
        // dd($registry, $request_splits);
        $register = Accounting::createOrUpdateRegistry($registry, $registry_record_type);
        $splits = Accounting::createOrUpdateSplits($register, $request_splits, $registry_record_type, $splits_records_type);

        $remove = array_get($request, 'remove', []);
        if(count($remove) > 0){
            foreach ($remove as $item) {
                $split = RegisterSplit::find($item);
                if(!is_null($split)){
                    $partner_split = RegisterSplit::find(array_get($split, 'splits_partner_id'));
                    if ($partner_split) $partner_split->forceDelete();
                    $split->forceDelete();
                }
            }
        }

        return response()->json(['success' => true]);
    }


    public function update_original(Request $request)
    {
        $reg = $request->input('register');
        $rows = $request->input('splits');
        $extra_data = $request->input('extra_data');
        $init_cred = false;
        $init_deb = false;

        foreach ($rows as $key => $r) {
            $rows[$key]['contact_id'] = Crypt::decrypt($r['contact_id']);
            $rows[$key]['fund_id'] = Crypt::decrypt($r['fund_id']);
            $rows[$key]['account_id'] = Crypt::decrypt($r['account_id']);

            if (array_key_exists('id', $rows[$key])) {
                $rows[$key]['id'] = Crypt::decrypt($r['id']);
            }
        }
        $reg['id'] = Crypt::decrypt($reg['id']);
        $reg['account_register_id'] = Crypt::decrypt($reg['account_register_id']);

        if ($reg['credit'] !== null && $reg['credit'] !== '') {
            if ($extra_data['aol'] === 'asset') {
                $reg['amount'] = $reg['amount'] * -1;
            } else if ($extra_data['aol'] === 'liability') {
                $reg['amount'] = $reg['amount'];
            }
            $init_cred = true;
            $init_deb = false;
        } else if ($reg['debit'] !== null && $reg['debit'] !== '') {
            if ($extra_data['aol'] === 'liability') {
                $reg['amount'] = $reg['amount'] * -1;
            } else if ($extra_data['aol'] === 'asset') {
                $reg['amount'] = $reg['amount'];
            }
            $init_cred = false;
            $init_deb = true;
        }
        $register = Register::findOrFail($reg['id']);
        foreach ($reg as $key => $val) {
            if ($key === 'date') {
                $reg[$key] = Carbon::parse($reg[$key])->toDateString();
            }
            $register->$key = $reg[$key];
        }
        $register->save();
        $totalSplitsId = RegisterSplit::where('register_id', $reg['id'])->where('account_id', '!=', $reg['account_register_id'])->pluck('id')->toArray();

        foreach($rows as $split) {
            if (isset($split['id']) && ($key = array_search($split['id'], $totalSplitsId)) !== false) {
                unset($totalSplitsId[$key]);
            }
        }
        if (!empty($totalSplitsId)) {
            $splitsForDelete = RegisterSplit::findMany($totalSplitsId);
            foreach($splitsForDelete as $del) {
                $splitPartnerDelete = RegisterSplit::find($del->splits_partner_id);
                $splitPartnerDelete->forceDelete();
                $del->forceDelete();
            }
        }
        foreach($rows as $key => $split) {
            $auto_split_amount = 0;
            $entered_split_amount = 0;
            $cred = false;
            $deb = false;

            if ($reg['amount'] > 0) {
                $auto_split_amount = abs($split['amount']);
            } else if ($reg['amount'] < 0) {
                $auto_split_amount = abs($split['amount']) * -1;
            }

            if (array_key_exists('id', $split) && $split['id'] !== null) {
                $spl = RegisterSplit::findOrFail($split['id']);
                $spl_auto = RegisterSplit::where('splits_partner_id', $split['id'])->first();
                if ($init_cred) {
                    $spl_auto->credit = abs($auto_split_amount);
                    $spl_auto->debit = null;
                } else {
                    $spl_auto->credit = null;
                    $spl_auto->debit = abs($auto_split_amount);
                }
                $spl_auto->amount = $auto_split_amount;
                $spl_auto->fund_id = $split['fund_id'];
                $spl_auto->contact_id = $split['contact_id'];
                $spl_auto->save();

                $acc_id = $split['account_id'];
                $group_id = Account::where('id', $acc_id)->value('account_group_id');
                $g = AccountGroup::where('id', $group_id)->value('chart_of_account');

                if ($init_cred && $extra_data['aol'] === 'asset') {
                    if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $deb = true;
                    } else if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount;
                        $cred = true;
                    }
                } else if ($init_deb && $extra_data['aol'] === 'asset') {
                    if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $cred = true;
                    } else if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount;
                        $deb = true;
                    }
                } else if ($init_cred && $extra_data['aol'] === 'liability') {
                    if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $deb = true;
                    } else if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount;
                        $cred = true;
                    }
                } else if ($init_deb && $extra_data['aol'] === 'liability') {
                    if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $cred = true;
                    } else if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount;
                        $deb = true;
                    }
                }
                if ($cred) {
                    $spl->credit = abs($entered_split_amount);
                    $spl->debit = null;
                } else if ($deb){
                    $spl->credit = null;
                    $spl->debit = abs($entered_split_amount);
                }
                $spl->contact_id = $split['contact_id'];
                $spl->fund_id = $split['fund_id'];
                $spl->account_id = $split['account_id'];
                $spl->amount = $entered_split_amount;
                $spl->comment = $split['comment'];
                $spl->tag = $split['tag'];
                $spl->save();
            } else {
                $split_auto = mapModel(new RegisterSplit, $split);
                if ($init_cred) {
                    array_set($split_auto, 'credit', abs($auto_split_amount));
                } else {
                    array_set($split_auto, 'debit', abs($auto_split_amount));
                }
                array_set($split_auto, 'amount', $auto_split_amount);
                array_set($split_auto, 'contact_id', $split['contact_id']);
                array_set($split_auto, 'fund_id', $split['fund_id']);
                array_set($split_auto, 'account_id', $register->account_register_id);
                array_set($split_auto, 'tenant_id', $register->tenant_id);
                array_set($split_auto, 'register_id', $register->id);
                $split_auto->save();

                $account_id = $split['account_id'];
                $group_id = Account::where('id', $account_id)->value('account_group_id');
                $g = AccountGroup::where('id', $group_id)->value('chart_of_account');

                if ($init_cred && $extra_data['aol'] === 'asset') {
                    if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $deb = true;
                    } else if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount;
                        $cred = true;
                    }
                } else if ($init_deb && $extra_data['aol'] === 'asset') {
                    if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $cred = true;
                    } else if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount;
                        $deb = true;
                    }
                } else if ($init_cred && $extra_data['aol'] === 'liability') {
                    if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $deb = true;
                    } else if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount;
                        $cred = true;
                    }
                } else if ($init_deb && $extra_data['aol'] === 'liability') {
                    if ($g === 'liability' || $g === 'equity' || $g === 'expense') {
                        $entered_split_amount = $auto_split_amount * -1;
                        $cred = true;
                    } else if ($g === 'asset' || $g === 'income') {
                        $entered_split_amount = $auto_split_amount;
                        $deb = true;
                    }
                }

                $split = mapModel(new RegisterSplit, $split);
                if ($cred) {
                    array_set($split, 'credit', abs($entered_split_amount));
                } else if ($deb){
                    array_set($split, 'debit', abs($entered_split_amount));
                }
                array_set($split, 'contact_id', $split['contact_id']);
                array_set($split, 'fund_id', $split['fund_id']);
                array_set($split, 'account_id', $split['account_id']);
                array_set($split, 'tenant_id', $register->tenant_id);
                array_set($split, 'register_id', $register->id);
                array_set($split, 'splits_partner_id', $split_auto->id);
                array_set($split, 'amount', $entered_split_amount);
                $split->save();
                $s = RegisterSplit::find($split_auto->id);
                $s->splits_partner_id = $split->id;
                $s->save();
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!auth()->user()->can('accounting-delete')) abort(403);
        try {
            $register = Register::find(Crypt::decrypt($id));
        } catch (\Throwable $th) {
            $register = Register::find($id);
        }

        if(! $register) {
            return response()->json(['success' => false]);
        }
        //delete splits one by one so we can trigger observer event
        foreach (array_get($register, 'splits', []) as $split) {
            $split->delete();
        }
        
        // unlink linked bank transaction (if it exists)
        $register->bankTransaction()->update([
            'register_id'=>null,
            'mapped'=>0,
        ]); 
        //not sure why is this completly removing record (not soft delete)
        //then the db foreign key will complete delete the splits records
        $register->forceDelete();

        return response()->json(['success' => true]);
    }

    public function getNextEntryNumber(Request $request){
        $register = Register::withTrashed()->whereIn('register_type', ['journal_entry', 'fund_transfer'])->orderBy('id', 'desc')->get()->first();
        return array_get($register, 'journal_entry_id', 0) + 1;
    }

    public function getCreditOrDebitTitles(Request $request){
        $titles = Accounting::getCreditOrDebitTitles(array_get($request, 'account_register_id'));
        return response()->json($titles);
    }

    private function appendSearchToQuery($request,$subQuery)
    {
        $contact_name = array_get($request,'contact_name');
        $contact_email = array_get($request,'contact_email');
        $amount = array_get($request,'amount');
        $account = array_get($request,'account');
        $from = array_get($request, 'from') ? array_get($request, 'from').' 00:00:00' : null;
        $to = array_get($request, 'to') ? array_get($request, 'to').' 23:59:59' : null;
        if ($contact_name) $subQuery = $subQuery->whereRaw("concat_ws(' ',first_name,last_name) like '%$contact_name%'");
        if ($contact_email) $subQuery = $subQuery->where(function ($query) use ($contact_email) {
            $query->where('email_1', $contact_email)->orWhere('email_2',$contact_email);
        });
        if (array_get($request,'check_number')) $subQuery = $subQuery->where('re.check_number',array_get($request,'check_number'));
        if (array_get($request,'comment')) $subQuery = $subQuery->where('searched_splits.comment',array_get($request,'comment'));
        if (array_get($request,'tag')) $subQuery = $subQuery->where('searched_splits.tag',array_get($request,'tag'));
        if ($from && $to) $subQuery = $subQuery->whereBetween('re.date',[$from,$to]);
        if (empty($from) && $to) $subQuery = $subQuery->where('re.date','<=',$to);
        if ($from && empty($to)) $subQuery = $subQuery->where('re.date','>=',$from);
        if ($account) $subQuery = $subQuery->where('acc.name','like',"%$account%");

        if ($amount) $subQuery = $subQuery->havingRaw("SUM(searched_splits.amount) = $amount");

        return $subQuery;
    }
}
