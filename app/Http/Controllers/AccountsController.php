<?php

namespace App\Http\Controllers;

use Response;
use Validator;

use App\Constants;
use App\Http\Requests;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Fund;
use App\Models\StartingBalance;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;

class AccountsController extends Controller
{
    const PERMISSION = 'accounting-accounts';

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
    public function index()
    {
        if (!auth()->user()->can('accounting-view')) abort(403);

        // this array controls whether or not the create/edit/delete buttons are displayed on the page
        $permissions = array_get(auth()->user()->ability([],[
            'accounting-create',
            'accounting-update',
            // TODO improve 'fool-proofing' for avoiding orphaned data and confirming deleting
            // 'accounting-delete', 
        ],['return_type'=>'array']),'permissions');

        return view('accounts.index', compact('permissions'));
    }

    public function bulkUpdate(Requests\Accounting\Update $request)
    {
        $user = User::find($request->input('user')['id']);
        $accounts = $request->input('account');
        if (empty($accounts)) return response('No data passed', 200);

        foreach($accounts as $account) {
            $acc = Account::findOrFail($account['id']);
            if ($acc) {
                $acc->order = $account['order'];
                $acc->account_group_id = $account['account_group_id'];
                $acc->save();
            }
        }

        return response('', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\Accounting\StoreAccount $request)
    {
        $user = User::find($request->input('user')['id']);
        $request_account = $request->input('account');

        if ($request->account['account_fund_id'] === 0) $request_account['account_fund_id'] = null;

        $account = mapModel(new Account(), $request_account);

        $request_account_number = $request->input('account.number');
        if ($request_account_number && Account::where('number', $request_account_number)->where('id', '!=', $account->id)->first()) {
            return response("Account with number $request_account_number already exists. Please assign a different number", 409);
        }
        $group = AccountGroup::where('id', $account->account_group_id)->value('chart_of_account');

        $account_type = array_get($request, 'account.account_type');
        if(empty($account_type)){
            $account_type = $this->getAccountType($group);
            if(!is_null($account_type)){
                array_set($account, 'account_type', $account_type);
            }
        }

        if (!$user->tenant->account()->save($account)) return response('Something went wrong', 500);

        if ($group === 'asset' || $group === 'liability') {
            $funds = Fund::all();

            foreach ($funds as $fund) {
                $startingBalance = new StartingBalance();
                $startingBalance->balance = 0;
                $startingBalance->tenant_id = $request->input('user')['tenant_id'];
                $startingBalance->fund_id = $fund->id;
                $startingBalance->account_id = $account->id;
                $startingBalance->save();
            }
        }

        return $account;
    }

    protected function getAccountType($group){
        $type = null;
        switch ($group) {
            case 'asset':
                $type = array_get(Constants::ACCOUNT_TYPES, 'ASSET');
                break;
            case 'liability':
                $type = array_get(Constants::ACCOUNT_TYPES, 'LIABILITY');
                break;
            case 'equity':
                $type = array_get(Constants::ACCOUNT_TYPES, 'EQUITY');
                break;
            case 'income':
                $type = array_get(Constants::ACCOUNT_TYPES, 'INCOME');
                break;
            case 'expense':
                $type = array_get(Constants::ACCOUNT_TYPES, 'EXPENSES');
                break;
            default:

                break;
        }
        return $type;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('accounting-view')) abort(403);
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
        if (!auth()->user()->can('accounting-update')) abort(403);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('accounting-update')) abort(403);

        $user = User::find($request->input('user')['id']);
        $account = Account::findOrFail($id);
        $input = $request->input('account');
        $acc = array();
        if (!$user->can('accounting-update')) abort(500);

        $request_account_number = $request->input('account.number');
        if ($request_account_number && Account::where('number', $request_account_number)->where('id', '!=', $account->id)->first()) {
            return response("Account with number $request_account_number already exists. Please assign a different number", 409);
        }
        if ($request->account['account_fund_id'] == 0) {
            $account->account_fund_id = null;
            unset($input['account_fund_id']);
        }

        foreach ($input as $key => $i) {
            if (isset($i)) {
                $account->$key = $i;
                if ($key == 'account_group_id') {
                    $account->subAccounts()->update([
                        'account_group_id' => $i,
                    ]);
                }
            }
        }

        if (!$account->sub_account) $account->parent_account_id = null;
        $account->save();

        return $account;
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

        $account = Account::findOrFail($id);
        $account->forceDelete();
        return $id;
    }

    public function comingSoon(Request $request){
        return view('accounting.accounting_coming_soon');
    }

    public function subscribeComingSoon(Request $request){
        return redirect()->back()->with(['msg' => 'success']);
    }

    public function autocompleteAccounts(Request $request)
    {
        $search = array_get($request, 'search');
        $accounts = Account::with('accountGroup:id,chart_of_account')
        ->where('name', 'like', "%$search%")
        ->orderBy('name') ;

        if(!is_null(array_get($request, 'account_type'))){
            $accounts->where('account_type', array_get($request, 'account_type'));
        }

        if($scopes = array_get($request, 'scopes')) {
            if (!is_array($scopes)) $scopes = [$scopes];
            foreach ($scopes as $scope) {
                $accounts->$scope();
            }
        }

        $accounts = $accounts->get();

        // custom relationship sort
        $accounts = $this->sortByChartCategory($accounts);

        $result = collect($accounts)->reduce(function($result, $account) {
            $category = ucfirst($account->accountGroup->chart_of_account);
            $singular = in_array($category, ['Income','Expense']);

            $account['category'] = $singular ? $category : str_plural($category);
            $account['label'] = $account->name;
            $account['value'] = $account->name;
            $account['data'] = Crypt::encrypt($account->id);
            $account['id'] = $account->id;

            array_push($result, $account);
            return $result;
        }, []);
        return $result;
    }

    /**
     * Sorts a collection of Account object such that the order matches the list
     * chart of account categories on the accounts page
     *
     * Tip: to sort alphabetically within categories, simply pre-sort by the account's name
     *
     * @param  Collection $accounts A collection of Account objects
     * @return Collection           sorted collection
     */
    protected function sortByChartCategory($accounts) {
        $order = ['asset','liability','equity','income','expense'];

        return $accounts->sort(function ($a, $b) use ($order) {
            return array_search($a->accountGroup->chart_of_account, $order)
            - array_search($b->accountGroup->chart_of_account, $order);
        });
    }
}
