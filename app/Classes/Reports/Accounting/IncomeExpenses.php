<?php

namespace App\Classes\Reports\Accounting;

use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\AccountGroup;
use Carbon\Carbon;

/**
 * Description of Settings
 *
 * @author josemiguel
 */
class IncomeExpenses {

    public function getDateRange($request){
        $id = array_get($request, 'range', 1);
        $range = [
            'start' => Carbon::now()->startOfMonth(),
            'end' => Carbon::now()->endOfDay()
        ];
        switch ($id) {
            case 1:
                $range = [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfDay()
                ];
                break;
            case 2:
                $range = [
                    'start' => Carbon::now()->subMonthsNoOverflow(1)->startOfMonth(),
                    'end' => Carbon::now()->subMonthsNoOverflow(1)->endOfMonth()
                ];
                break;
            case 3:
                $range = [
                    'start' => Carbon::now()->startOfQuarter(),
                    'end' => Carbon::now()->endOfQuarter()
                ];
                break;
            case 4:
                $range = [
                    'start' => Carbon::now()->subQuarters(1)->startOfQuarter(),
                    'end' => Carbon::now()->subQuarters(1)->endOfQuarter()
                ];
                break;
            case 5:
                $range = [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfDay()
                ];
                break;
            case 6:
                $range = [
                    'start' => Carbon::now()->subYearsNoOverflow(1)->startOfYear(),
                    'end' => Carbon::now()->subYearsNoOverflow(1)->endOfYear()
                ];
                break;
            case 7:
                $range = [
                    'start' => Carbon::parse(array_get($request, 'start_date'))->startOfDay(),
                    'end' => Carbon::parse(array_get($request, 'end_date'))->endOfDay(),
                ];
                break;
            default:
                $range = [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfDay()
                ];
                break;
        }

        return $range;
    }
    
    public function getBuilder($funds = null, $select = 'income_statement'){
        $select_fields = [
            'account_groups.chart_of_account',
            DB::raw('account_groups.id as group_id'),
            DB::raw('account_groups.name as group_name'),
            DB::raw('accounts.id as account_id'),
            'accounts.account_group_id',
            DB::raw('accounts.number as account_number'),
            DB::raw('accounts.name as account_name'),
            DB::raw('SUM(register_splits.amount) as amount'),
            DB::raw('accounts.sub_account as is_sub_account'),
            DB::raw('accounts.parent_account_id as parent_account_id'),
            DB::raw('(select sum(rs2.amount) from register_splits rs2 join accounts a2 on a2.id = rs2.account_id where a2.parent_account_id = accounts.id or a2.id = accounts.id) as amount_with_sub'),
            DB::raw('funds.name as fund_name')
        ];

        if($select == 'income_statement_by_month'){
            array_push($select_fields, DB::raw('MONTH(registers.date) as month_id'));
            array_push($select_fields, DB::raw("DATE_FORMAT(registers.date, '%b') as month_name"));
        }

        if($select == 'income_statement_by_fund'){
            array_push($select_fields, DB::raw('funds.id as fund_id'));
        }

        $builder = AccountGroup::join('accounts', 'accounts.account_group_id', 'account_groups.id')
            ->leftJoin('register_splits', 'register_splits.account_id', 'accounts.id')
            ->leftJoin('registers', 'registers.id', 'register_splits.register_id')
            ->leftJoin('funds', 'funds.id', 'register_splits.fund_id')
            ->select($select_fields)
            ->whereIn('account_groups.chart_of_account', ['income','expense']);
            
            if(!is_null($funds)){
                $builder->whereIn('funds.id', $funds);
            }
            
        return $builder;
    }

    public function getIncomeExpenseStatementByFund($start_date = null, $end_date = null, $funds = null){
        //by default we get current month
        if(is_null($start_date)){
            $start_date = Carbon::now()->startOfMonth();
        }
        if(is_null($end_date)){
            $end_date = Carbon::now()->endOfMonth();
        }
        $builder = $this->getBuilder($funds, 'income_statement_by_fund');
        $builder->whereBetween('registers.date', [$start_date, $end_date])
            ->groupBy('account_groups.id', 'accounts.id', 'funds.id')
            ->orderBy('account_groups.chart_of_account', 'asc')
            ->orderBy('account_groups.name', 'asc')
            ->orderBy('accounts.number', 'asc');

        return $builder->get();
    }

    public function getIncomeExpenseStatementByMonth($start_date = null, $end_date = null, $funds = null){
        //by default we get current month
        if(is_null($start_date)){
            $start_date = Carbon::now()->startOfMonth();
        }
        if(is_null($end_date)){
            $end_date = Carbon::now()->endOfMonth();
        }
        // HACK (e.g., '2020-04-01' BETWEEN '2020-03-31 23:59:59' AND '2020-04-30 23:59:59')
        if (get_class($start_date) == Carbon::class) $start_date = $start_date->subSeconds(1);
        // \Log::info($start_date);
        $builder = $this->getBuilder($funds, 'income_statement_by_month');
        $builder->whereBetween('registers.date', [$start_date, $end_date])
            ->groupBy('account_groups.id', 'accounts.id', DB::raw('MONTH(registers.date)'))
            ->orderBy('account_groups.chart_of_account', 'asc')
            ->orderBy('account_groups.name', 'asc')
            ->orderBy('accounts.number', 'asc');

        return $builder->get();
    }

    public function getAccountGroups($groups = null, $funds = null){
        $builder = $this->getBuilder($funds);
        $builder->groupBy('account_groups.id')
            ->orderBy('account_groups.name');
        if(!is_null($groups)){
            $builder->whereIn('account_groups.id', $groups);
        }
        return $builder->get();
    }

    public function getIncomeExpenseStatement($start_date = null, $end_date = null, $funds = null){
        //by default we get current month
        if(is_null($start_date)){
            $start_date = Carbon::now()->startOfMonth();
        }
        if(is_null($end_date)){
            $end_date = Carbon::now()->endOfMonth();
        }
        
        $builder = $this->getBuilder($funds);
        $builder->whereBetween('registers.date', [$start_date, $end_date])
            ->groupBy('account_groups.id', 'accounts.id')
            ->orderBy('account_groups.chart_of_account', 'asc')
            ->orderBy('account_groups.name', 'asc')
            ->orderBy('accounts.number', 'asc');
        
        return $builder->get();
    }

    public function addParentGroupsToIncomeStatement(&$statement)
    {
        $counter = 0;
        $parentAccountsToAdd = [];
        
        foreach ($statement as $account) {
            $accountNumbers = $statement->pluck('account_number')->toArray();
            
            if (array_get($account, 'is_sub_account') === 1) {
                $parentAccount = Account::find(array_get($account, 'parent_account_id'));
                
                if ($parentAccount && !in_array(array_get($parentAccount, 'number'), $accountNumbers)) {
                    $statement->splice($counter, 0, [[
                        'chart_of_account' => array_get($parentAccount, 'group.chart_of_account'),
                        'group_id' => array_get($parentAccount, 'group.id'),
                        'group_name' => array_get($parentAccount, 'group.name'),
                        'account_id' => array_get($parentAccount, 'id'),
                        'account_group_id' => array_get($parentAccount, 'account_group_id'),
                        'account_number' => array_get($parentAccount, 'number'),
                        'account_name' => array_get($parentAccount, 'name'),
                        'amount' => null,
                        'is_sub_account' => 0,
                        'parent_account_id' => null,
                        'amount_with_sub' => 1,
                        'fund_name' => null
                    ]]);
                    
                    $counter++;
                }
            }
            
            $counter++;
        }
    }
}
