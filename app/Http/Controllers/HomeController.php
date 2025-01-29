<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index() {
        $min = Carbon::now()->subMonths(1);
        $max = Carbon::now();
        
        $totalAmounByDay = DB::table('transactions')
                ->join('transaction_splits', 'transactions.id', 'transaction_splits.transaction_id')
                ->select(DB::raw('CAST(transactions.transaction_initiated_at AS DATE) as days, sum(transaction_splits.amount) as amount, count(CAST(transactions.transaction_initiated_at AS DATE)) as transactions'))
                ->where([
                    ['transactions.status', '=', 'complete'],
                    ['transactions.tenant_id', '=', auth()->user()->tenant->id]
                ])
                ->whereBetween('transactions.transaction_initiated_at', [$min, $max])
                ->groupBy(DB::raw('CAST(transactions.transaction_initiated_at as DATE)'))
                ->get();

        $dataset = [];
        $labels = [];
        $serie = [];
        $total = [];

        foreach ($totalAmounByDay as $t) {
            array_push($labels, $t->days);
            array_push($serie, (double) $t->amount);
            array_push($total, $t->transactions);
        }

        $data = [];
        array_set($data, 'label', 'Total Amount');
        array_set($data, 'serie', $serie);
        
        array_set($data, 'backgroundColor', 'rgba(54,162,235,0.2)');
        array_set($data, 'borderColor', '#36A2EB');
        array_set($data, 'pointBackgroundColor', 'rgba(54,162,235,1)');
        array_set($data, 'pointBorderColor', '#36A2EB');

        array_push($dataset, $data);
        
        $data = [];

        array_set($data, 'label', 'Total Transactions');
        array_set($data, 'serie', $total);
        
        array_set($data, 'backgroundColor', 'rgba(255,206,86,0.2)');
        array_set($data, 'borderColor', '#FFCE56');
        array_set($data, 'pointBackgroundColor', 'rgba(255,206,86,1)');
        array_set($data, 'pointBorderColor', '#FFCE56');


        array_push($dataset, $data);

        $line = [
            'labels' => $labels,
            'dataset' => $dataset
        ];

        $pie = DB::table('transactions')
                ->join('transaction_splits', 'transactions.id', 'transaction_splits.transaction_id')
                ->select(DB::raw('UPPER(transactions.device_category) as device, sum(transaction_splits.amount) as total'))
                ->where([
                    ['transactions.status', '=', 'complete'],
                    ['transactions.tenant_id', '=', auth()->user()->tenant->id]
                ])
                ->whereBetween('transactions.transaction_initiated_at', [$min, $max])
                ->groupBy('transactions.device_category')
                ->get();
        
        $labels = [];
        $serie = [];
        foreach ($pie as $p) {
            array_push($labels, $p->device);
            array_push($serie, $p->total);
        }
        $pie1 = ['labels' => $labels, 'serie' => $serie];


        $pie = DB::table('transactions')
                ->join('transaction_splits', 'transactions.id', 'transaction_splits.transaction_id')
                ->select(DB::raw('UPPER(transactions.transaction_path) as path, sum(transaction_splits.amount) as total'))
                ->where([
                    ['transactions.status', '=', 'complete'],
                    ['transactions.tenant_id', '=', auth()->user()->tenant->id]
                ])
                ->whereBetween('transactions.transaction_initiated_at', [$min, $max])
                ->groupBy('transactions.transaction_path')
                ->get();

        $labels = [];
        $serie = [];
        foreach ($pie as $p) {
            array_push($labels, $p->path);
            array_push($serie, $p->total);
        }
        $pie2 = ['labels' => $labels, 'serie' => $serie];

        $params = [
            'line' => $line,
            'pie1' => $pie1,
            'pie2' => $pie2,
            'from' => date("F jS, Y", strtotime($min)),
            'to' => date("F jS, Y", strtotime($max)),
        ];
        
        if( auth()->user()->can('dashboard-view') ){
            return view('home')->with($params);
        }
        else{
            return redirect()->route('contacts.edit', ['id' => auth()->user()->contact->id]);
        }
    }

}
