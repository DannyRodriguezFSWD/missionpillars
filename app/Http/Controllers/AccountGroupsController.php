<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Fund;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\StartingBalance;

use DB;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use function Symfony\Component\Debug\Tests\testHeader;

class AccountGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $accounts = Account::orderBy('order')->get()->toArray();
        $groups = AccountGroup::with(['accounts' => function($query) use ($request){
            $query->getBaseQuery()->orders = null;
            if ($request->has('sort_by')){
                foreach ($request->sort_by as $sort) {
                    $query->orderBy($sort);
                }
            }
            return $query;
        }, 'accounts.fund'])->orderBy('name', 'asc')->get()->toArray();
        // dd($groups);
        $info = [];
        foreach ($groups as $key => $t) {
            if (!empty($t['accounts'])) {
                foreach ($t['accounts'] as $k => $v) {
                    $v['sub_list'] = array();
                    $t['accounts'][$k] = $v;

                    if ($v['sub_account'] == true && $v['parent_account_id'] !== null) {
                        $info[] = ['acc' => $v, 'parent' => $v['parent_account_id']];
                    }
                }

            }
            $groups[$key] = $t;
        }

        if ($info) {
            foreach ($info as $i) {
                foreach ($groups as $key => $t) {
                    foreach ($t['accounts'] as $k => $acc) {
                        if (isset($acc['id'])) {
                            if ($acc['id'] == $i['parent']) {
                                $acc['sub_list'][] = $i['acc'];
                                $t['accounts'][$k] = $acc;
                            }

                            if ($acc['id'] == $i['acc']['id']) {
                                unset($t['accounts'][$k]);
                            }
                        }
                    }
                    $t['accounts'] = array_values($t['accounts']);
                    $groups[$key] = $t;
                    // var_dump('test 1');
                }
            }
        }

        // dd($groups);
        $data['accounts'] = collect(array_values($accounts));
        $data['groups'] = collect(array_values($groups));
        $data['funds'] = Fund::latest()->get()->prepend(['id' => 0, 'name' => 'None']);

        // dd($data);

        return $data;

    }

    public function bulkUpdate(Request $request)
    {
        $user = User::find($request->input('user')['id']);
        $input = $request->input('account');
        if (!empty($input)) {
            if ($user->can('group-update')) {
                foreach ($input as $account) {
                    $acc = AccountGroup::findOrFail($account['id']);
                    if ($acc) {
                        $acc->order = $account['order'];
                        $acc->save();
                    }
                }
                return response('', 200);

            }
            return response('', 401);
        } else {
            return response('No data passed', 200);
        }
        return response('Something went wrong', 500);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\Accounting\StoreAccountGroup $request)
    {
        $user = User::find($request->input('user')['id']);
        $group = mapModel(new AccountGroup(), $request->input('group'));

        if (!$user->tenant->accountGroups()->save($group)) abort(500);

        return $group;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     // 
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\Accounting\Update $request, $id)
    {
        $user = User::find($request->input('user')['id']);
        $group = AccountGroup::findOrFail($id);
        if ($user->can('group-update')) {
            if ($request->input('name')) {
                $group->name = $request->input('name');
            }
            if ($request->input('chart')) {
                $group->chart_of_account = $request->input('chart');
            }
            $group->save();
            return $group;
        }

        abort(500);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        AccountGroup::destroy($id);
        return '';
    }

    public function showFunds()
    {

    }

    public function createFunds(Requests\Accounting\Store $request)
    {
        $user = User::find($request->input('user')['id']);
        $fund = mapModel(new Fund(), $request->input('fund'));

        if (!$user->tenant->funds()->save($fund)) {
            abort(500, 'Error saving fund');
        }

        $group = AccountGroup::where('chart_of_account', 'equity')
            ->where('deleted_at', null)->latest()->get();

        if ($group->isEmpty()) {
            $gr = [
                'name' => 'General Fund',
                'chart_of_account' => 'equity'
            ];
            $group = mapModel(new AccountGroup(), $gr);

            if (!$user->tenant->accountGroups()->save($group)) {
                abort(500, 'error saving account group');
            }
        }
        $accountGroups = AccountGroup::where('chart_of_account', 'asset')
            ->orWhere('chart_of_account', 'liability')->with('accounts')->get();

        foreach ($accountGroups as $accountGroup) {

            foreach ($accountGroup->accounts as $account) {
                $startingBalance = new StartingBalance();
                $startingBalance->tenant_id = $request->input('user')['tenant_id'];
                $startingBalance->account_id = $account->id;
                $startingBalance->fund_id = $fund['id'];
                $startingBalance->balance = 0;
                $startingBalance->save();
            }
        }

        $group_ids = AccountGroup::where('chart_of_account', 'equity')
            ->whereNull('deleted_at')->pluck('id');
        $increment = Account::orderBy('number', 'desc')
            ->whereIn('account_group_id', $group_ids)->whereNull('deleted_at')->first();
        $acc = [
            'name' => $request->input('fund')['name'] . " Fund Balance",
            'account_group_id' => $group_ids->first(),
            'number' => ($increment ? $increment->number + 1 : 3000),
            'account_fund_id' => $fund->id,
        ];
        $account = mapModel(new Account(), $acc);

        if (!$user->tenant->account()->save($account)) abort(500, 'error saving fund account');

        $fund->account_id = $account->id;
        $fund->save();
        $data = [
            'account' => $account,
            'group' => AccountGroup::where('id', $account->account_group_id)->pluck('name'),
            'fund' => $fund
        ];

        return $data;
    }

    /**
     * Allows updating the name of a fund
     * @param  [type]                   $id      [description]
     * @param RequestsAccountingUpdate $request [description]
     * @return [type]                            [description]
     */
    public function updateFund($id, Requests\Accounting\Update $request)
    {

        $fund = Fund::findOrFail($id);

        if ($fund->account->number != $request->account_number)
            if (Account::where('number', $request->account_number)->exists()) return response('Account number already exist!', 409);

        $fund->account->number = $request->account_number;
        $fund->name = $request->name;
        if (!$fund->save()) abort(500);
        $fund->account->name = $request->name . " Fund Balance";
        if (!$fund->account->save()) abort(500);

        return $fund->load('account');
    }

    public function autocompleteFunds(Request $request)
    {

        $accounts = Fund::where(function ($query) use ($request) {

            if (is_null($request->input('account_fund_id'))) {
                $query->where('name', 'like', "%" . $request->input('search') . "%");
            } else {
                $query->where('id', $request->input('account_fund_id'));
            }
        })->get();
        $result = collect($accounts)->reduce(function ($result, $accounts) {
            $accounts['label'] = array_get($accounts, 'name');
            $accounts['value'] = array_get($accounts, 'name');
            $accounts['data'] = Crypt::encrypt(array_get($accounts, 'id'));
            $accounts['id'] = array_get($accounts, 'id');
            array_push($result, $accounts);
            return $result;
        }, []);

        return $result;
    }
}
