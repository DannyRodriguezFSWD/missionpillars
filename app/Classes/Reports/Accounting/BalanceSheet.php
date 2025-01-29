<?php
namespace App\Classes\Reports\Accounting;

use App\Models\Fund;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceSheet {
    public $asset = [];
    public $liability = [];
    public $equity = [];
    
    protected $by_funds;
    protected $date;
    protected $filtered_fund_ids = [];
    protected $tenant_id;
    
    public function __construct($request = [], $by_funds = false) {
        try {
            $this->date = Carbon::parse(array_get($request, 'date'))->endOfDay();
        } catch (\Throwable $th) {
            $this->date = Carbon::now()->endOfDay();
        }
        $this->by_funds = $by_funds;
        
        $this->filtered_fund_ids = array_get($request, 'fund_ids', []);
        $this->tenant_id = Auth::user()->tenant_id;
        
        $this->initReport();
    }
    
    public function toArray() {
        return [
            'asset' => $this->asset,
            'liability' => $this->liability,
            'equity' => $this->equity,
        ];
    }
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function equityRegisterSplitsQuery()
    {
        $balance_register_splits = DB::table('register_splits as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, sum(rs.amount) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('registers as r', 'r.id', 'rs.register_id')
        ->join('accounts as a', function ($join) {
            // ALL register splits are linked directly 
            $join->on('rs.account_id', 'a.id');
            
            // If journal entry or fund transfer, additionally link register splts through the fund
            $join->orOn('f.account_id','a.id')
            // DON'T NEED TO INCLUDE JOURNAL ENTRIES!
            ->whereIn('r.register_type',['fund_transfer']);
        })
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->whereIn('f.id', $this->filtered_fund_ids)
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag.chart_of_account', ['asset', 'liability', 'equity'])
        ->where('r.date', '<=', $this->date);
        if($this->by_funds){
            $balance_register_splits->groupBy('a.id', 'f.id');
        }
        else{
            $balance_register_splits->groupBy('a.id');
        }
        
        return $balance_register_splits;
    }
    
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function incomeRegisterSplitsQuery()
    {
        $income_register_splits = DB::table('register_splits as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, sum(rs.amount) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('accounts as a', 'a.id', 'f.account_id')
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->join('accounts as a1', 'a1.id', 'rs.account_id')
        ->join('account_groups as ag1', 'ag1.id', 'a1.account_group_id')
        ->join('registers as r', 'r.id', 'rs.register_id')
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag1.chart_of_account', ['income'])
        ->whereIn('f.id', $this->filtered_fund_ids)
        ->where('r.date', '<=', $this->date);
        if($this->by_funds){
            $income_register_splits->groupBy('a.id', 'f.id');
        }
        else{
            $income_register_splits->groupBy('a.id');
        }
        
        return $income_register_splits;
    }
    
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function expenseRegisterSplitsQuery()
    {
        $expense_register_splits = DB::table('register_splits as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, -1 * sum(rs.amount) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('accounts as a', 'a.id', 'f.account_id')
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->join('accounts as a1', 'a1.id', 'rs.account_id')
        ->join('account_groups as ag1', 'ag1.id', 'a1.account_group_id')
        ->join('registers as r', 'r.id', 'rs.register_id')
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag1.chart_of_account', ['expense'])
        ->whereIn('f.id', $this->filtered_fund_ids)
        ->where('r.date', '<=', $this->date);
        
        if($this->by_funds){
            $expense_register_splits->groupBy('a.id', 'f.id');
        }
        else{
            $expense_register_splits->groupBy('a.id');
        }
        
        return $expense_register_splits;
    }
    
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function startingBalanceQuery() 
    {
        $builder_starting_balances = DB::table('starting_balances as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, sum(rs.balance) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('accounts as a', 'a.id', 'rs.account_id')
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag.chart_of_account', ['asset', 'liability'])
        ->whereIn('f.id', $this->filtered_fund_ids);
        if($this->by_funds){
            $builder_starting_balances->groupBy('a.id', 'f.id');
        }
        else{
            $builder_starting_balances->groupBy('a.id');
        }
        
        return $builder_starting_balances;
    }
    
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function equityStartingBalanceQuery()
    {
        $assets_equity_starting_balance = DB::table('starting_balances as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, sum(rs.balance) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('accounts as a', 'a.id', 'f.account_id')
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->join('accounts as a1', 'a1.id', 'rs.account_id')
        ->join('account_groups as ag1', 'ag1.id', 'a1.account_group_id')
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag1.chart_of_account', ['asset'])
        ->whereIn('f.id', $this->filtered_fund_ids);
        if($this->by_funds){
            $assets_equity_starting_balance->groupBy('a.id', 'f.id');
        }
        else{
            $assets_equity_starting_balance->groupBy('a.id');
        }
        
        return $assets_equity_starting_balance;
    }
    
    
    /**
     * @return Illuminate\Database\Query\Builer
     */
    public function liabilityStartingBalancyQuery()
    {
        $assets_liability_starting_balance = DB::table('starting_balances as rs')
        ->select(DB::raw('ag.chart_of_account, a.id as account, a.name, a.number, f.id as fund, f.name as fund_name, -1 * sum(rs.balance) as amount'))
        ->join('funds as f', 'f.id', 'rs.fund_id')
        ->join('accounts as a', 'a.id', 'f.account_id')
        ->join('account_groups as ag', 'ag.id', 'a.account_group_id')
        ->join('accounts as a1', 'a1.id', 'rs.account_id')
        ->join('account_groups as ag1', 'ag1.id', 'a1.account_group_id')
        ->where('rs.tenant_id', $this->tenant_id)
        ->whereIn('ag1.chart_of_account', ['liability'])
        ->whereIn('f.id', $this->filtered_fund_ids);
        if($this->by_funds){
            $assets_liability_starting_balance->groupBy('a.id', 'f.id');
        }
        else{
            $assets_liability_starting_balance->groupBy('a.id');
        }
        
        return $assets_liability_starting_balance;
    }
    
    /**
     * @return Illuminate\Support\Collection
     */
    public function getAssetLiabilityGroups() 
    {
        $balance_register_splits = $this->equityRegisterSplitsQuery();
        $income_register_splits = $this->incomeRegisterSplitsQuery();
        $expense_register_splits = $this->expenseRegisterSplitsQuery();
        
        $builder_starting_balances = $this->startingBalanceQuery();
        $assets_equity_starting_balance = $this->equityStartingBalanceQuery();
        $assets_liability_starting_balance = $this->liabilityStartingBalancyQuery();
        
        
        $builder_unions = $balance_register_splits
        ->union($income_register_splits)
        ->union($expense_register_splits)
        ->union($builder_starting_balances)
        ->union($assets_equity_starting_balance)
        ->union($assets_liability_starting_balance);
        
        $raw_query_asset_liability_groups = DB::table(DB::raw("({$builder_unions->toSql()}) as combined_tables"))
        ->select(DB::raw('chart_of_account, account, name, number, fund, fund_name, sum(amount) as amount'))
        ->mergeBindings($builder_unions);
        
        if($this->by_funds){
            $raw_query_asset_liability_groups->groupBy(['account', 'fund']);
        }
        else{
            $raw_query_asset_liability_groups->groupBy('account');
        }
        return $raw_query_asset_liability_groups->get();
    }
    
    protected function initReport()
    {
        $asset_liability_groups = $this->getAssetLiabilityGroups();
        
        if(!$this->by_funds){
            // dd($asset_liability_groups);
            foreach ($asset_liability_groups as $group) {
                $account_type = $group->chart_of_account;
                $amount = $group->amount;
                $data = [
                    'balance' => $amount,
                    'number' => intval($group->number),
                    'name' => $group->name,
                    'fund_name' => $group->fund_name,
                ];
                
                $this->$account_type[$group->account . "-" . $group->fund] = $data;
            }
        }
        else{ // By Fund
            foreach ($asset_liability_groups as $group) {
                $account_type = $group->chart_of_account;
                $account_id = $group->account;
                $amount = $group->amount;
                $current_account = [
                    'type' => $account_type,
                    'id' => $group->account,
                    'name' => $group->name,
                    'number' => intval($group->number),
                    'balance' => 0,
                    'funds' => []
                ];
                
                foreach ($asset_liability_groups as $sub_loop_groups) {
                    if(array_get($current_account, 'type') == $account_type && array_get($current_account, 'id') == $sub_loop_groups->account){
                        $data = [
                            'balance' => $sub_loop_groups->amount,
                            'number' => intval($sub_loop_groups->number),
                            'name' => $sub_loop_groups->fund_name,
                            'id' => $sub_loop_groups->fund
                        ];
                        $current_account['balance'] = $current_account['balance'] + $sub_loop_groups->amount;
                        array_push($current_account['funds'], $data);
                    }
                }
                $this->$account_type[$account_id] = $current_account;
            }
        }
    }
    
}
