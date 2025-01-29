<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Constants;
use App\Http\Requests;
use App\Models\AccountGroup;
use App\Models\Fund;
use App\Models\Register;
use App\Models\RegisterSplit;
use App\Traits\AmountTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class JournalEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use AmountTrait;

    const PERMISSION = 'accounting-journal-entry';

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
        $groups = AccountGroup::with('accounts')->orderBy('name', 'asc')->get();
        $funds = Fund::all();
        $max_journal_entry_id = Register::maxJournalEntryId();

        // this array controls whether or not the create/edit/delete buttons are displayed on the page
        $permissions = array_get(auth()->user()->ability([],[
            'accounting-create',
            'accounting-update',
            'accounting-delete',
        ],['return_type'=>'array']),'permissions');

        return view('journal_entries.index')
            ->with(compact('max_journal_entry_id','groups','funds', 'permissions'));
    }

    public function fundTransfers() {
        if (!auth()->user()->can('accounting-view')) abort(403);
        $groups = AccountGroup::with('accounts')->orderBy('name', 'asc')->get();
        $funds = Fund::all();
        $all_records = Register::withTrashed()->with('splits')->where('register_type', '=', 'fund_transfer')->get();
        foreach ($all_records as $key => $record) {
            $all_records[$key]->fund_id = $record->splits[1]->fund_id;
            $all_records[$key]->account_id = $record->account_register_id;
        }

        $max_journal_entry_id = Register::maxJournalEntryId();
        $fund_transfer_groups = 'fund_transfer_groups';


        // this array controls whether or not the create/edit/delete buttons are displayed on the page
        $permissions = array_get(auth()->user()->ability([],[
            'accounting-create',
            'accounting-update',
            'accounting-delete',
        ],['return_type'=>'array']),'permissions');

        return view('journal_entries.funds')
            ->with( compact('max_journal_entry_id','groups', 'fund_transfer_groups',
            'all_records', 'funds', 'permissions') );
    }

    public function getSplits(Request $request)
    {

        $acc_id = $request->input('register_id');
        $reg_id = Register::where('id', $acc_id)->value('account_register_id');
        $select_fields = [
            'spl.*',
            'acc.name as account_name',
            'acc.number as account_number',
            'f.name as fund_name',
            'spl.account_id as @acc_id',
            'r.fund_transfer_amount'
        ];

        if(array_get($request, 'register_type') != 'fund_transfer'){
            array_push($select_fields, 'c.first_name as first_name');
            array_push($select_fields, 'c.last_name as last_name');
            array_push($select_fields, 'c.email_1 as email');
        }

        $query = DB::table('register_splits as spl')
            ->select($select_fields)
            ->join('registers as r', 'spl.register_id', '=', 'r.id');

            if(array_get($request, 'register_type') != 'fund_transfer'){
                $query->join('contacts as c', 'spl.contact_id', '=', 'c.id');
            }

            $query->join('accounts as acc', 'spl.account_id', '=', 'acc.id')
            ->join('funds as f', 'spl.fund_id', '=', 'f.id')
            ->where('r.id', $acc_id);

            if(array_get($request, 'register_type') != 'fund_transfer'){
                $query->where(function ($query) use($acc_id, $reg_id) {
                    $query->where('spl.account_id', '!=', $reg_id);
                });
            }

        $splits = $query->get();

        $register = Register::where('id', $acc_id)->first();
        $s = $this->processRegisterData($splits, true);
        $r = $this->processRegisterData($register);
        return response()->json([
            'splits' => $s,
            'register' => $r
        ]);

        //$result = json_encode(['splits' => $s, 'register' => $r]);
        //return $result;
    }

    public function processRegisterData($query_results, $splits = false)
    {
        $result = array();
        if ($splits) {
            foreach($query_results as $split) {
                $r['amount'] = $split->amount;
                $r['credit'] = $split->credit;
                $r['debit'] = $split->debit;
                $r['comment'] = $split->comment;
                $r['tag'] = $split->tag;
                $r['display_fund_id'] = $split->fund_id;
                $r['fund_id'] = $split->fund_id;
                $r['display_account_id'] = $split->account_id;
                $r['account_id'] = $split->account_id;
                $r['contact_id'] = $split->contact_id;
                $r['id'] = $split->id;

                if(isset($split->first_name)){
                    $r['contact'] = $split->first_name . ' ' . $split->last_name . ' (' . $split->email . ')';
                }

                $r['fund_name'] = $split->fund_name;
                $r['account_name'] = $split->account_name;
                $result[] = $r;
            }
        } else {
            $result['display_id'] = $query_results->id;
            $result['id'] = $query_results->id;
            $result['account_register_id'] = $query_results->account_register_id;
            $result['fund_transfer_amount'] = $query_results->fund_transfer_amount;
            $result['account_id'] = $query_results->account_register_id;
            $result['amount'] = $query_results->amount;
            $result['credit'] = $query_results->credit;
            $result['debit'] = $query_results->debit;
            $result['date'] = $query_results->date;
            $result['comment'] = $query_results->comment;
            $result['check_number'] = $query_results->check_number;
            $result['journal_entry_id'] = $query_results->journal_entry_id;
            $result['display_journal_entry_id'] = $query_results->journal_entry_id;
            $result['register_type'] = $query_results->register_type;
        }
        return $result;
    }

    public function fetchData()
    {
        return Register::maxJournalEntryId();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        request()->session()->flash('doCreate','1');
        return redirect()->route('journal-entries.index');
    }
    public function fundTransfersCreate()
    {
        request()->session()->flash('doCreate','1');
        return redirect()->route('journal-entries.fund-transfers');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\Accounting\Store $request)
    {
        $register = mapModel(new Register, $request->input('register'));
        array_set($register, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
        array_set($register, 'date', Carbon::parse($request->input('register')['date'])->toDateString());
        $register->save();

        foreach ($request->input('rows') as $split) {
            $split_entered = mapModel(new RegisterSplit, $split);
            $amount_type = !empty($split['credit']) ? 'credit' : 'debit';
            $account_id = Crypt::decrypt($split['account_id']);
            $entered_split_amount = $this->getEnteredAmountByAccountId($account_id, $split['amount'], $amount_type);
            array_set($split_entered, 'fund_id', Crypt::decrypt($split['fund_id']));
            array_set($split_entered, 'contact_id', Crypt::decrypt($split['contact_id']));
            array_set($split_entered, 'account_id', Crypt::decrypt($split['account_id']));
            array_set($split_entered, 'tenant_id', $register->tenant_id);
            array_set($split_entered, 'register_id', $register->id);
            array_set($split_entered, 'amount', $entered_split_amount);
            $split_entered->save();
        }

        return $register;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fundTransfersStore(Request $request)
    {
        $latest_journal_entry_id = Register::maxJournalEntryId();

        $register_data = $request->input('register');
        $account_id = $register_data['account_id'];
        $amount = $register_data['fund_transfer_amount'];

        $register = mapModel(new Register, $register_data);
        array_set($register, 'journal_entry_id', $latest_journal_entry_id + 1);

        array_set($register, 'account_register_id', $account_id);
        array_set($register, 'amount', $amount);
        array_set($register, 'tenant_id', array_get(auth()->user(), 'tenant.id'));
        array_set($register, 'date', Carbon::parse($request->input('register')['date'])->toDateString());
        $register->save();

        $source_fund_split = mapModel(new RegisterSplit, $register_data);
        array_set($source_fund_split, 'fund_id', array_get($request, 'register.source_fund_id'));

        $target_fund_split = mapModel(new RegisterSplit, $register_data);
        array_set($target_fund_split, 'fund_id', array_get($request, 'register.target_fund_id'));

        $amount = -1 * $register['amount'];

        //array_set($source_fund_split, 'fund_id', $register_data['source_fund_id']);
        array_set($source_fund_split, 'account_id', $account_id);
        array_set($source_fund_split, 'tenant_id', $register->tenant_id);
        //array_set($source_fund_split, 'contact_id', 1);//original
        array_set($source_fund_split, 'contact_id', null);
        array_set($source_fund_split, 'register_id', $register->id);
        $amount_type = $this->getAmountTypeByAccountId($account_id, $amount);
        array_set($source_fund_split, $amount_type, abs($amount));
        array_set($source_fund_split, 'amount', $amount);
        $source_fund_split->save();

        $amount = $register['amount'];

        //array_set($target_fund_split, 'fund_id', $register_data['fund_id']);
        array_set($target_fund_split, 'account_id', $account_id);
        array_set($target_fund_split, 'tenant_id', $register->tenant_id);
        //array_set($target_fund_split, 'contact_id', 1);//original
        array_set($source_fund_split, 'contact_id', null);
        array_set($target_fund_split, 'register_id', $register->id);
        $amount_type = $this->getAmountTypeByAccountId($account_id, $amount);
        array_set($target_fund_split, $amount_type, abs($amount));
        array_set($target_fund_split, 'amount', $amount);
        array_set($target_fund_split, 'splits_partner_id', $source_fund_split->id);
        $target_fund_split->save();
        $source_fund_split->splits_partner_id = $target_fund_split->id;
        $source_fund_split->save();

        return $register;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\Accounting\Update $request, $id)
    {
        try {
            $decoded_id = Crypt::decrypt($id);
            $register = Register::findOrFail($decoded_id);
        } catch (\Throwable $th) {
            $register = Register::findOrFail($id);
        }

        mapModel($register, array_get($request, 'register'));

        $register->save();

        $totalSplitsId = RegisterSplit::where('register_id', $id)->pluck('id')->toArray();
        if (count($totalSplitsId) !== count($request->input('splits'))) {
            foreach($request->input('splits') as $split) {
                if (isset($split['id']) && ($key = array_search($split['id'], $totalSplitsId)) !== false) {
                    unset($totalSplitsId[$key]);
                }
            }
            RegisterSplit::destroy($totalSplitsId);
        }

        if ($request->input('splits')) {
            foreach ($request->input('splits') as $split) {
                if (array_key_exists('id', $split)) {
                    $spl = RegisterSplit::findOrFail($split['id']);

                    $spl->fund_id = $split['fund_id'];
                    $spl->account_id = $split['account_id'];
                    $spl->amount = $split['amount'];
                    $spl->comment = $split['comment'];
                    $spl->tag = $split['tag'];
                    $spl->save();
                } else {
                    $split = mapModel(new RegisterSplit, $split);
                    array_set($split, 'tenant_id', $register->tenant_id);
                    array_set($split, 'register_id', $register->id);
                    $split->save();
                }

            }
        }

        return $register;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
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
        //not sure why is this completly removing record (not soft delete)
        //then the db foreign key will complete delete the splits records
        $register->forceDelete();

        return response()->json(['success' => true]);
    }
}
