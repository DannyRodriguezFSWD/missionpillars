<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Requests\Accounting;
use App\Models\AccountGroup;
use App\Models\Fund;
use App\Models\User;
use App\Models\StartingBalance;

use Illuminate\Http\Request;

class StartingBalancesController extends Controller
{
    const PERMISSION = 'accounting-starting-balances';

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
        
        $groups = AccountGroup::where('chart_of_account', '=', 'asset')
        ->orWhere('chart_of_account', '=', 'liability')
        ->with('accounts')->orderBy('chart_of_account', 'asc')
        ->get();
        $funds = Fund::with('account')->get();
        $balances = StartingBalance::all();
        $permissions = array_get(auth()->user()->ability([],[
            'accounting-update',
        ],['return_type'=>'array']),'permissions');
        
        return view('starting_balances.index', compact('permissions','groups', 'funds', 'balances'));
    }

    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(Accounting\Store $request)
    {
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
    public function update(Accounting\Update $request, $id)
    {
        $input = $request->input('sb');
        
        if (empty($input)) return response('Something went wrong!', 500);
        
        $attr = [
            'account_id' => $id,
            'fund_id' => $input['fund_id'],
            'tenant_id' => $request->input('user')['tenant_id']
        ];
        
        $sb = StartingBalance::firstOrNew($attr);
        $sb->balance = $input['balance'];
        if (!$sb->save()) return response('Error saving starting balance', 500);
        
        return response('Successfully updated account balance', 200);
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
        //
    }
}
