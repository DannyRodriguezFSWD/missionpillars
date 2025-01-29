<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Classes\Reports\Accounting\BalanceSheet;
use App\Classes\Reports\Accounting\IncomeExpenses;
use App\Constants;
use App\Models\AccountGroup;
use App\Models\Fund;
use App\Models\RegisterSplit;
use App\Models\StartingBalance;
use App\Models\User;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('reports-view')) abort(403);
            return $next($request);
        });
    }

    public function index()
    {
        return view('reports.index');
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function balanceSheet(Request $request)
    {
        $funds = Fund::all();

        if ($request->ajax()) {
            return $this->getFilteredDataBalanceSheet($request);
        }

        return view('accounting_reports.balance_sheet', compact('funds'));
    }


    public function getFilteredDataBalanceSheet(Request $request, $by_funds = false)
    {
        $report = new BalanceSheet($request, $by_funds);
        return $report->toArray();
    }

    public function getFundStatus($date, $fund_ids = false)
    {
        $accounts = AccountGroup::where('chart_of_account', '!=', 'equity')->with('accounts')->get();
        if (!$fund_ids) {
            $fund_ids = Fund::all()->pluck('id');
        }
        $report = [];
        foreach ($fund_ids as $fund) {
            foreach ($accounts as $account) {
                foreach ($account->accounts as $acc) {
                    $report[$fund]['balance'] = $this->getBalanceSheetByFund($acc->id, $date, [$fund], 'funds_total');
                    $report[$fund]['name'] = Fund::where('id', $fund)->value('name');
                }
            }
        }
        return $report;
    }

    public function getBalanceSheetByFund($acc_id, $date, $fund_ids, $type = null, $is_type = null)
    {
        $total = 0;

        if ($type !== 'income_statement' && $type !== 'funds_total' && $type !== 'isbymonth') {
            $query1 = DB::table('starting_balances');
            for ($i = 0; $i < count($fund_ids); $i++) {
                if ($i == 0) {
                    $query1->where('account_id', $acc_id)->where('fund_id', $fund_ids[$i]);
                } else {
                    $query1->orWhere('account_id', $acc_id)->where('fund_id', $fund_ids[$i]);
                }
            }
            $sb = $query1->sum('balance');
            $total = $sb;
        } else if ($type === 'funds_total') {
            $query1 = DB::table('starting_balances');
            $query1->where('fund_id', $fund_ids[0]);
            $sb = $query1->sum('balance');
            $total = $sb;
        }

        $query2 = DB::table('registers')
            ->join('register_splits', 'register_splits.register_id', '=', 'registers.id');

        for ($i = 0; $i < count($fund_ids); $i++) {
            $f_id = $fund_ids[$i];
            if ($i == 0) {
                $query2->orWhere(function ($query) use ($acc_id, $date, $f_id) {
                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('registers.account_register_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        } else {
                            $q->where('registers.date', '<=', $date);
                        }
                    });
                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('register_splits.account_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        } else {
                            $q->where('registers.date', '<=', $date);
                        }
                    });
                });
            } else {
                $query2->orWhere(function ($query) use ($acc_id, $date, $f_id) {

                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('registers.account_register_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        } else {
                            $q->where('registers.date', '<=', $date);
                        }
                    });
                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('register_splits.account_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        } else {
                            $q->where('registers.date', '<=', $date);
                        }
                    });
                });
            }
        }

        $transactions = $query2->get(['register_splits.amount', 'register_splits.account_id', 'registers.account_register_id']);
        foreach ($transactions as $index => $transaction) {
            $total = $total + $transaction->amount;
        }

        return $total;
    }

    public function getDataIsByMonth($acc_id, $date, $fund_ids, $type = null, $is_type = null)
    {
        $transaction = [];
        $total = [];
        foreach ($date as $key => $month) {
            $query2 = DB::table('registers')
                ->join('register_splits', 'register_splits.register_id', '=', 'registers.id');

            for ($i = 0; $i < count($fund_ids); $i++) {
                $f_id = $fund_ids[$i];
                if ($i == 0) {
                    $query2->orWhere(function ($query) use ($acc_id, $month, $f_id) {
                        $query->orWhere(function ($q) use ($acc_id, $month, $f_id) {
                            $q->orWhere('registers.account_register_id', $acc_id)->where('register_splits.fund_id', $f_id);
                            if (is_array($month)) {
                                $q->whereBetween('registers.date', [$month['start'], $month['end']]);
                            }
                        });
                        $query->orWhere(function ($q) use ($acc_id, $month, $f_id) {
                            $q->orWhere('register_splits.account_id', $acc_id)->where('register_splits.fund_id', $f_id);
                            if (is_array($month)) {
                                $q->whereBetween('registers.date', [$month['start'], $month['end']]);
                            }
                        });
                    });
                } else {
                    $query2->orWhere(function ($query) use ($acc_id, $month, $f_id) {

                        $query->orWhere(function ($q) use ($acc_id, $month, $f_id) {
                            $q->orWhere('registers.account_register_id', $acc_id)->where('register_splits.fund_id', $f_id);
                            if (is_array($month)) {
                                $q->whereBetween('registers.date', [$month['start'], $month['end']]);
                            }
                        });
                        $query->orWhere(function ($q) use ($acc_id, $month, $f_id) {
                            $q->orWhere('register_splits.account_id', $acc_id)->where('register_splits.fund_id', $f_id);
                            if (is_array($month)) {
                                $q->whereBetween('registers.date', [$month['start'], $month['end']]);
                            }
                        });
                    });
                }
            }
            $transactions = $query2->get(['register_splits.amount', 'register_splits.account_id', 'registers.account_register_id']);
            $total[$key] = 0;
            foreach ($transactions as $index => $transaction) {
                if ($acc_id == $transaction->account_id && $type !== 'isbymonth') {
                    $transaction->amount = $transaction->amount * -1;
                }
                $total[$key] = $total[$key] + $transaction->amount;
            }
        }

        return $total;
    }

    
    public function getDataIsByFund($acc_id, $date, $fund_ids, $type = null, $is_type = null)
    {

        $total = [];
        foreach ($fund_ids as $key => $f_id) {
            $query2 = DB::table('registers')
                ->join('register_splits', 'register_splits.register_id', '=', 'registers.id');

                $query2->orWhere(function ($query) use ($acc_id, $date, $f_id) {
                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('registers.account_register_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        }
                    });
                    $query->orWhere(function ($q) use ($acc_id, $date, $f_id) {
                        $q->orWhere('register_splits.account_id', $acc_id)->where('register_splits.fund_id', $f_id);
                        if (is_array($date)) {
                            $q->whereBetween('registers.date', [$date['start'], $date['end']]);
                        }
                    });
                });
            $transactions = $query2->get(['register_splits.amount', 'register_splits.account_id', 'registers.account_register_id', 'register_splits.fund_id']);
            $total[$key] = 0;
            foreach ($transactions as $index => $transaction) {
                $total[$key] = $total[$key] + $transaction->amount;
            }
        }

        return $total;
    }

    public function getISData(Request $request)
    {
        $income_statement = 'income_statement';
        $fund_ids = $request->input('fund_ids');
        if (empty($fund_ids)) {
            $fund_ids = Fund::all()->pluck('id');
        }
        $income = AccountGroup::where('chart_of_account', '=', 'income')->with('accounts')->get();
        $expense = AccountGroup::where('chart_of_account', '=', 'expense')->with('accounts')->get();
        $date = json_decode($request->input('date'), true);
        if ($request->input('type') && ($request->input('type') == 'isbymonth' || $request->input('type') == 'isbyfund')) {
            $report = ['report_data' => ['income' => [], 'expense' => []], 'date_ranges' => []];
            $income_statement = $request->input('type');
            if($request->input('type') == 'isbymonth') {
                if ($date['start'] == '' || $date['end'] == '') {
                    $date = $this->yearToDate();
                } else {
                    $date = $this->formatDate($date);
                }
            } elseif ($request->input('type') == 'isbyfund') {
                if ($date['start'] == '' || $date['end'] == '') {
                    $date = [
                        'start' => Carbon::now()->startOfMonth()->subMonth(date('n') - 1)->startOfMonth()->toDateString(),
                        'end' => Carbon::now()->toDateString(),
                    ];
                }
            }
            $report['report_data']['income'] = $this->ISData($date, $income, 'income', $fund_ids, $income_statement);
            $report['report_data']['expense'] = $this->ISData($date, $expense, 'expense', $fund_ids, $income_statement);
            $report['date_ranges'] = $date;
        } else {
            $report = ['income' => [], 'expense' => []];
            if ($date['start'] == '' || $date['end'] == '') {
                $date = [
                    'end' => Carbon::now()->toDateString(),
                    'start' => Carbon::now()->startOfMonth()->toDateString()
                ];
            }
            $report['income'] = $this->ISData($date, $income, 'income', $fund_ids, $income_statement);
            $report['expense'] = $this->ISData($date, $expense, 'expense', $fund_ids, $income_statement);
        }
        return $report;
    }

    public function incomeStatement(Request $request)
    {
        $funds = array_get($request, 'funds', Fund::all());
        $report_statement = new IncomeExpenses();
        $date_range = $report_statement->getDateRange($request);

        $account_groups = $report_statement->getAccountGroups(null, $funds);
        $statement = $report_statement->getIncomeExpenseStatement(array_get($date_range, 'start'), array_get($date_range, 'end'), $funds);
        $report_statement->addParentGroupsToIncomeStatement($statement);
        
        $total_income = $statement->where('chart_of_account', 'income')->sum('amount');
        $total_expense = $statement->where('chart_of_account', 'expense')->sum('amount');
        
        $report = [
            'income' => [],
            'expense' => [],
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'date_range' => $date_range
        ];

        foreach ($account_groups as $group) {
            $accounts = [];
            $total_group_amount = 0;
            foreach ($statement as $account) {
                if(array_get($group, 'group_id') == array_get($account, 'account_group_id')){
                    $total_group_amount = $total_group_amount + array_get($account, 'amount', 0);
                    array_push($accounts, $account);
                }
            }
            array_set($group, 'total_group_amount', $total_group_amount);
            if(array_get($group, 'chart_of_account') == 'income'){
                array_push($report['income'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
            else{
                array_push($report['expense'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
        }
        
        if($request->ajax()){
            return response()->json($report);
        }
        
        return view('accounting_reports.income_statement')->with('funds', $funds);
        
    }

    public function incomeStatementByMonth(Request $request)
    {
        $funds = array_get($request, 'funds', Fund::all());
        $report_statement = new IncomeExpenses();
        $date_range = $report_statement->getDateRange($request);

        $account_groups = $report_statement->getAccountGroups(null, $funds);
        $statement = $report_statement
        ->getIncomeExpenseStatementByMonth(array_get($date_range, 'start'), array_get($date_range, 'end'), $funds);
        
        $total_income = $statement->where('chart_of_account', 'income')->sum('amount');
        $total_expense = $statement->where('chart_of_account', 'expense')->sum('amount');

        $current_month = Carbon::now()->month;
        $current_months = [];

        $starting_i = 0;
        $current_month = is_null(array_get($date_range, 'end')) ? $current_month : array_get($date_range, 'end')->month;

        if (in_array(array_get($request, 'range'), [1,2])){ // this month, last month
            $starting_i = $current_month - 1;
        }
        elseif(in_array(array_get($request, 'range'), [3,4,7])){ // this quarter, last quarter, custom
            $starting_i = is_null(array_get($date_range, 'end')) ? 0 : array_get($date_range, 'start')->month - 1;
            $current_month = is_null(array_get($date_range, 'end')) ? $current_month : array_get($date_range, 'end')->month;
        }
        elseif(in_array(array_get($request, 'range'), [6])){//last year
            $current_month = 12;
        }

        for($i = $starting_i; $i < $current_month; $i++){//months
            $month_id = $i+1;
            $current_months[$i] = [
                'id' => $month_id,
                'name' => array_get(Constants::SHORT_MONTH_NAMES, $i),
            ];
        }
        
        $report = [
            'income' => [],
            'expense' => [],
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'date_range' => $date_range,
            'current_months' => $current_months
        ];

        foreach ($account_groups as $group) {
            $accounts = [];
            $total_group_amount = 0;
            foreach ($statement->groupBy('account_id') as $account_id => $account_months) {
                if(array_get($group, 'group_id') == array_get($account_months->first(), 'account_group_id')){
                    $months = [];
                    $total_year = 0;
                    $account_number = $account_months->first()->account_number;
                    $account_name = $account_months->first()->account_name;
                    
                    // initialize months from start month to current monh
                    for($i = $starting_i; $i < $current_month; $i++){
                        $month_id = $i+1;
                        $months[$i] = [
                            'id' => $month_id,
                            'name' => array_get(Constants::SHORT_MONTH_NAMES, $i),
                            'amount' => 0
                        ];
                    }
                    
                    foreach($account_months as $account_month) {
                        
                        // if reported month is included add to total and set month amount
                        if ($starting_i < $account_month->month_id && $account_month->month_id <= $current_month) {
                            $total_group_amount += array_get($account_month, 'amount', 0);
                            $total_year += $account_month->amount;
                            array_set($months[$account_month->month_id-1], 'amount', $account_month->amount);
                        } 
                        
                    }
                    
                    // Add to list of accounts
                    $accounts[] = compact('account_id','account_number','account_name','months','total_year');
                }
            }
            array_set($group, 'total_group_amount', $total_group_amount);
            if(array_get($group, 'chart_of_account') == 'income'){
                array_push($report['income'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
            else{
                array_push($report['expense'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
        }
        
        if($request->ajax()){
            return response()->json($report);
        }
        return view('accounting_reports.income_statement_by_month')->with('funds', $funds);
    }

    public function incomeStatementByFund(Request $request)
    {
        $funds = array_get($request, 'funds', Fund::all());
        //$funds = array_pluck(Fund::all(), 'id');
        $report_statement = new IncomeExpenses();
        $date_range = $report_statement->getDateRange($request);

        $account_groups = $report_statement->getAccountGroups(null, $funds);
        $statement = $report_statement->getIncomeExpenseStatementByFund(array_get($date_range, 'start'), array_get($date_range, 'end'), $funds);
        
        $total_income = $statement->where('chart_of_account', 'income')->sum('amount');
        $total_expense = $statement->where('chart_of_account', 'expense')->sum('amount');

        $current_funds = Fund::whereIn('id', $funds)->get();
        $report = [
            'income' => [],
            'expense' => [],
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'date_range' => $date_range,
            'current_funds' => $current_funds
        ];
        
        foreach ($account_groups as $group) {
            $accounts = [];
            $total_group_amount = 0;
            $previous_account_index = 0;
            foreach ($statement as $index => $account) {
                $last_account = array_get($statement, $index-1);
                if(array_get($group, 'group_id') == array_get($account, 'account_group_id')){
                    $total_group_amount = $total_group_amount + array_get($account, 'amount', 0);
                    if(array_get($last_account, 'account_id') != array_get($account, 'account_id')){
                        array_push($accounts, $account);
                        $previous_account_index++;
                    }
                }

                $total_funds = 0;
                foreach ($current_funds as $key => $fund) {
                    $items[$key] = [
                        'id' => array_get($fund, 'id'),
                        'name' => array_get($fund, 'name'),
                        'amount' => 0
                    ];
                    if(array_get($fund, 'id') == array_get($account, 'fund_id')){
                        $total_funds = $total_funds + array_get($account, 'amount', 0);
                        if(array_get($last_account, 'account_id') == array_get($account, 'account_id')){
                            $items = array_get($last_account, 'funds', []);
                            $total_funds = $total_funds + array_get($last_account, 'amount', 0);
                            array_set($items[$key], 'amount', array_get($account, 'amount', 0));
                            $account = $last_account;
                        }
                        else{
                            array_set($items[$key], 'amount', array_get($account, 'amount', 0));
                        }
                    }
                }
                array_set($account, 'funds', $items);
                array_set($account, 'total_funds', $total_funds);
            }

            array_set($group, 'total_group_amount', $total_group_amount);
            if(array_get($group, 'chart_of_account') == 'income'){
                array_push($report['income'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
            else{
                array_push($report['expense'], [
                    'group' => $group,
                    'accounts' => $accounts
                ]);
            }
        }
        
        if($request->ajax()){
            //return response()->json($request->all());
            return response()->json($report);
        }
        return view('accounting_reports.income_statement_by_fund')->with('funds', $funds);
    }

    public function yearToDate()
    {
        $dates = [];
        for ($i = date('n') - 1; $i >= 0; $i--) {
            if ($i !== 0) {
                $dates[Carbon::now()->startOfMonth()->subMonth($i)->format('M')] = [
                    'start' => Carbon::now()->startOfMonth()->subMonth($i)->startOfMonth()->toDateString(),
                    'end' => Carbon::now()->startOfMonth()->subMonth($i)->endOfMonth()->toDateString()
                ];
            } else {
                $dates[Carbon::now()->format('M')] = [
                    'start' => Carbon::now()->startOfMonth()->toDateString(),
                    'end' => Carbon::now()->toDateString()
                ];
            }
        }

        return $dates;
    }

    public function formatDate($date)
    {
        $dates = [];

        $start = (new \DateTime($date['start']))->modify('+1 day');
        $end = new \DateTime($date['end']);

        for ($i = $start; $i <= $end; $i->modify('+1 month')){
            $newMonth = clone $i;
            $dates[$newMonth->format('M')] = [
                'start' => $newMonth->format('Y-m-d'),
                'end' => $newMonth->modify('+1 month -1 day')->format('Y-m-d')
            ];
        }

        return $dates;
    }

    public function BSData($date, $accounts, $fund_ids)
    {
        $report = [];
        foreach ($accounts as $account) {
            foreach ($account->accounts as $acc) {
                $report[$acc->id]['balance'] = $this->getBalanceSheetByFund($acc->id, $date, $fund_ids);
                $report[$acc->id]['number'] = $acc->number;
                $report[$acc->id]['name'] = $acc->name;
            }
        }
        return $report;
    }

    public function ISData($date, $accounts, $type, $fund_ids, $is_type)
    {
        $income_statement = $is_type;
        $report = [];
        foreach ($accounts as $account) {
            foreach ($account->accounts as $acc) {
                if ($income_statement == 'income_statement') {
                    $report[$acc->id]['balance'] = $this->getBalanceSheetByFund($acc->id, $date, $fund_ids, $income_statement, $type);
                } else if($income_statement == 'isbyfund') {
                    $report[$acc->id]['balance'] = $this->getDataIsByFund($acc->id, $date, $fund_ids, $income_statement, $type);
                } else {
                    $report[$acc->id]['balance'] = $this->getDataIsByMonth($acc->id, $date, $fund_ids, $income_statement, $type);
                }
                $report[$acc->id]['number'] = $acc->number;
                $report[$acc->id]['name'] = $acc->name;
            }
        }
        return $report;
    }

    public function isPdfDownload(Request $request)
    {
        $income_statement = 'income_statement';
        $report = ['income' => [], 'expense' => []];
        $fund_ids = $request->input('fund_ids');
        $show_zeros = $request->input('show_zero');
        $date = $request->input('date');

        if (empty($fund_ids)) {
            $fund_ids = Fund::all()->pluck('id');
        }
        $funds = Fund::all();

        $income = AccountGroup::where('chart_of_account', '=', 'income')->with('accounts')->get();
        $expense = AccountGroup::where('chart_of_account', '=', 'expense')->with('accounts')->get();

        $report['income'] = $this->ISData($date, $income, 'income', $fund_ids, $income_statement);
        $report['expense'] = $this->ISData($date, $expense, 'expense', $fund_ids, $income_statement);

        $totals['income'] = 0;
        $totals['expense'] = 0;

        foreach ($report['income'] as $key => $income) {
            $totals['income'] += $income['balance'];
            $report['income'][$key] = $this->formatCurency($income, true);
        }

        foreach ($report['expense'] as $key => $expense) {
            $totals['expense'] += $expense['balance'] * -1;
            $report['expense'][$key] = $this->formatCurency($expense, true);
        }

        $totals['netIncome'] = $totals['income'] - $totals['expense'];

        $totals = $this->formatCurency($totals);

        $filename = 'Balance Sheet as of ' . date('m-d-Y_hi') . '.pdf';
        $pdf = PDF::loadView('accounting_reports.pdfs.income_statement_pdf', compact('report', 'funds', 'show_zeros', 'totals'));
        return $pdf->download($filename);

    }

    public function isByMonthPdfDownload(Request $request)
    {
        $income_statement = 'isbymonth';
        $report = ['income' => [], 'expense' => []];
        $fund_ids = $request->input('fund_ids');
        $show_zeros = $request->input('show_zero');
        $date = $request->input('date');
        $show_totals = $request->input('show_totals');
        $total_column_name = $request->input('total_column_name');

        if ($date['start'] == '' || $date['end'] == '') {
            $date = $this->yearToDate();
        } else {
            $date = $this->formatDate($date);
        }

        if (empty($fund_ids)) {
            $fund_ids = Fund::all()->pluck('id');
        }
        $funds = Fund::all();

        $income = AccountGroup::where('chart_of_account', '=', 'income')->with('accounts')->get();
        $expense = AccountGroup::where('chart_of_account', '=', 'expense')->with('accounts')->get();

        $report['income'] = $this->ISData($date, $income, 'income', $fund_ids, $income_statement);
        $report['expense'] = $this->ISData($date, $expense, 'expense', $fund_ids, $income_statement);

        foreach ($report['income'] as $key => $income) {
            if($show_totals == "true") {
                $report['income'][$key]['balance'][$total_column_name] = 0;
            }
            foreach ($income['balance'] as $month => $amount) {
                if($show_totals == "true") {
                    $report['income'][$key]['balance'][$total_column_name] += $amount;
                }
            }
        }

        foreach ($report['expense'] as $key => $expense) {
            if($show_totals == "true") {
                $report['expense'][$key]['balance'][$total_column_name] = 0;
            }
            foreach ($expense['balance'] as $month => $amount) {
                $report['expense'][$key]['balance'][$month] *= -1;
                if($show_totals == "true") {
                    $report['expense'][$key]['balance'][$total_column_name] -= $amount;
                }
            }
        }

        $totals = ['income' => [], 'expense' => []];

        foreach ($report['income'] as $key => $income) {
            foreach ($income['balance'] as $month => $amount) {
                $totals['income'][$month] = !isset($totals['income'][$month]) ? $amount : ($totals['income'][$month] + $amount);
            }
        }

        foreach ($report['expense'] as $key => $expense) {
            foreach ($expense['balance'] as $month => $amount) {
                $totals['expense'][$month] = !isset($totals['expense'][$month]) ? $amount : ($totals['expense'][$month] + $amount);
                $totals['netIncome'][$month] = $totals['income'][$month] - $totals['expense'][$month];
            }
        }

        foreach ($report as $type => $val) {
            foreach ($report[$type] as $key => $income) {
                $report[$type][$key]['balance'] = $this->formatCurency($income['balance'], true);
            }
        }

        foreach ($totals as $type => $val) {
            $totals[$type] = $this->formatCurency($val);
        }

        $filename = 'Balance Sheet as of ' . date('m-d-Y_hi') . '.pdf';
        $pdf = PDF::loadView('accounting_reports.pdfs.income_statement_by_month_pdf', compact('report', 'funds', 'show_zeros', 'totals', 'show_totals', 'total_column_name'));
        return $pdf->download($filename);
    }

    public function isByFundPdfDownload(Request $request)
    {
        $income_statement = 'isbymonth';
        $report = ['income' => [], 'expense' => []];
        $fund_ids = $request->input('fund_ids');
        $show_zeros = $request->input('show_zero');
        $date = $request->input('date');
        $show_totals = $request->input('show_totals');
        $total_column_name = $request->input('total_column_name');

        if ($date['start'] == '' || $date['end'] == '') {
            $date = $this->yearToDate();
        } else {
            $date = $this->formatDate($date);
        }

        if (empty($fund_ids)) {
            $fund_ids = Fund::all()->pluck('id');
        }
        $funds = Fund::all();

        $income = AccountGroup::where('chart_of_account', '=', 'income')->with('accounts')->get();
        $expense = AccountGroup::where('chart_of_account', '=', 'expense')->with('accounts')->get();

        $report['income'] = $this->ISData($date, $income, 'income', $fund_ids, $income_statement);
        $report['expense'] = $this->ISData($date, $expense, 'expense', $fund_ids, $income_statement);

        foreach ($report['income'] as $key => $income) {
            if($show_totals == "true") {
                $report['income'][$key]['balance'][$total_column_name] = 0;
            }
            foreach ($income['balance'] as $month => $amount) {
                if($show_totals == "true") {
                    $report['income'][$key]['balance'][$total_column_name] += $amount;
                }
            }
        }

        foreach ($report['expense'] as $key => $expense) {
            if($show_totals == "true") {
                $report['expense'][$key]['balance'][$total_column_name] = 0;
            }
            foreach ($expense['balance'] as $month => $amount) {
                $report['expense'][$key]['balance'][$month] *= -1;
                if($show_totals == "true") {
                    $report['expense'][$key]['balance'][$total_column_name] -= $amount;
                }
            }
        }

        $totals = ['income' => [], 'expense' => []];

        foreach ($report['income'] as $key => $income) {
            foreach ($income['balance'] as $month => $amount) {
                $totals['income'][$month] = !isset($totals['income'][$month]) ? $amount : ($totals['income'][$month] + $amount);
            }
        }

        foreach ($report['expense'] as $key => $expense) {
            foreach ($expense['balance'] as $month => $amount) {
                $totals['expense'][$month] = !isset($totals['expense'][$month]) ? $amount : ($totals['expense'][$month] + $amount);
                $totals['netIncome'][$month] = $totals['income'][$month] - $totals['expense'][$month];
            }
        }

        foreach ($report as $type => $val) {
            foreach ($report[$type] as $key => $income) {
                $report[$type][$key]['balance'] = $this->formatCurency($income['balance'], true);
            }
        }

        foreach ($totals as $type => $val) {
            $totals[$type] = $this->formatCurency($val);
        }

        $filename = 'Balance Sheet as of ' . date('m-d-Y_hi') . '.pdf';
        $pdf = PDF::loadView('accounting_reports.pdfs.income_statement_by_fund_pdf', compact('report', 'funds', 'show_zeros', 'totals', 'show_totals', 'total_column_name'));
        return $pdf->download($filename);
    }

    public function bsPdfDownload(Request $request)
    {
        $show_zeros = $request->input('show_zero');
        $date = $request->input('date');
        $report = $this->getFilteredDataBalanceSheet($request);
        $totals['assets'] = 0;
        $totals['liability'] = 0;
        $totals['equity'] = 0;

        foreach ($report['assets'] as $key => $account) {
            $totals['assets'] += $account['balance'];
            $report['assets'][$key] = $this->formatCurency($account, true);
        }

        foreach ($report['liability'] as $key => $account) {
            $totals['liability'] += $account['balance'];
            $report['liability'][$key] = $this->formatCurency($account, true);
        }

        foreach ($report['equity'] as $key => $account) {
            $totals['equity'] += $account['balance'];
            $report['equity'][$key] = $this->formatCurency($account, true);
        }
        $totals = $this->formatCurency($totals);

        $filename = 'Balance Sheet as of ' . date('m-d-Y_hi') . '.pdf';
        $pdf = PDF::loadView('accounting_reports.pdfs.balance_sheet_pdf', compact('report', 'funds', 'show_zeros', 'totals', 'date'));

        return $pdf->download($filename);
    }

    function formatCurency($totals, $accounts = false)
    {
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        foreach ($totals as $key => $total) {

            if (!function_exists('money_format')) {
                if (!$accounts) {
                    if ($total < 0) {
                        $totals[$key] = '(' . $this->money_format('%.2n', abs(floatval($total))) . ')';
                    } else {
                        $totals[$key] = $this->money_format('%.2n', floatval($total));
                    }
                } else {
                    if ($key == 'balance' ||
                        in_array($key, ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'YTD Amount', 'Quarter Amount'])) {
                        if ($total < 0) {
                            $totals[$key] = '(' . $this->money_format('%.2n', abs(floatval($total))) . ')';
                        } else {
                            $totals[$key] = $this->money_format('%.2n', floatval($total));
                        }
                    }
                }
            } else {
                if (!$accounts) {
                    if ($total < 0) {
                        $totals[$key] = '(' . $this->money_format('%.2n', abs(floatval($total))) . ')';
                    } else {
                        $totals[$key] = $this->money_format('%.2n', floatval($total));
                    }
                } else {
                    if ($key == 'balance' ||
                        in_array($key, ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'YTD Amount', 'Quarter Amount'])) {
                        if ($total < 0) {
                            $totals[$key] = '(' . $this->money_format('%.2n', abs(floatval($total))) . ')';
                        } else {
                            $totals[$key] = $this->money_format('%.2n', floatval($total));
                        }
                    }
                }
            }
        }
        return $totals;
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function compareBalanceSheetByFund(Request $request)
    {
        $funds = Fund::all();

        if ($request->ajax()) {
            return $this->getFilteredDataBalanceSheet($request, true);
        }

        return view('accounting_reports.compare_balance_sheet_by_fund', compact('funds'));
    }

    /*
        That it is an implementation of the function money_format for the
        platforms that do not it bear.

        The function accepts to same string of format accepts for the
        original function of the PHP.

        (Sorry. my writing in English is very bad)

        The function is tested using PHP 5.1.4 in Windows XP
        and Apache WebServer.
        */
    function money_format($format, $number) {
        $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
            '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
        if (setlocale(LC_MONETARY, 0) == 'C') {
            setlocale(LC_MONETARY, '');
        }
        $locale = localeconv();
        preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
        foreach ($matches as $fmatch) {
            $value = floatval($number);
            $flags = array(
                'fillchar' => preg_match('/\=(.)/', $fmatch[1], $match) ?
                    $match[1] : ' ',
                'nogroup' => preg_match('/\^/', $fmatch[1]) > 0,
                'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                    $match[0] : '+',
                'nosimbol' => preg_match('/\!/', $fmatch[1]) > 0,
                'isleft' => preg_match('/\-/', $fmatch[1]) > 0
            );
            $width = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
            $left = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
            $right = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
            $conversion = $fmatch[5];

            $positive = true;
            if ($value < 0) {
                $positive = false;
                $value *= -1;
            }
            $letter = $positive ? 'p' : 'n';

            $prefix = $suffix = $cprefix = $csuffix = $signal = '';

            $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
            switch (true) {
                case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                    $prefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                    $suffix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                    $cprefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                    $csuffix = $signal;
                    break;
                case $flags['usesignal'] == '(':
                case $locale["{$letter}_sign_posn"] == 0:
                    $prefix = '(';
                    $suffix = ')';
                    break;
            }
            if (!$flags['nosimbol']) {
                $currency = $cprefix .
                    ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                    $csuffix;
            } else {
                $currency = $cprefix .$csuffix;
            }
            $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';

            $value = number_format($value, $right, $locale['mon_decimal_point'], $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
            $value = @explode($locale['mon_decimal_point'], $value);

            $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
            if ($left > 0 && $left > $n) {
                $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
            }
            $value = implode($locale['mon_decimal_point'], $value);
            if ($locale["{$letter}_cs_precedes"]) {
                $value = $prefix . $currency . $space . $value . $suffix;
            } else {
                $value = $prefix . $value . $space . $currency . $suffix;
            }
            if ($width > 0) {
                $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                    STR_PAD_RIGHT : STR_PAD_LEFT);
            }

            $format = str_replace($fmatch[0], $value, $format);
        }
        return $format;
    }
    
    public function exportIncomeStatement($random, Request $request)
    {
        if ($request->has('funds')) {
            $funds = json_decode(array_get($request, 'funds'), true);
        } else {
            $funds = Fund::all();
        }
        
        $report_statement = new IncomeExpenses();
        $date_range = $report_statement->getDateRange($request);
        
        $account_groups = $report_statement->getAccountGroups(null, $funds);
        $statement = $report_statement->getIncomeExpenseStatement(array_get($date_range, 'start'), array_get($date_range, 'end'), $funds);
        
        $tail = str_replace(':', '', displayLocalDateTime(Carbon::now()->toDateTimeString())->toDateTimeString());
        $tail = str_replace('-', '', $tail);
        $tail = str_replace(' ', '-', $tail);
        $filename = substr(implode('-', ['income-statement', $tail]), 0, 28);
        $data = [
            'statement' => $statement,
            'filename' => $filename
        ];

        Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Sheetname' . time(), function($sheet) use ($data) {
                $sheet->setOrientation('portrait');
                $sheet->loadView('reports.accounting.income-statement.excel', $data);
                $sheet->setColumnFormat(array(
                    'B' => '0.00',
                ));
            });
        })->download('xlsx');
    }
}
