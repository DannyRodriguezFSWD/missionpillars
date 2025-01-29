<?php

namespace App\Classes\Shared\Widgets\Charts\Pie;

use Illuminate\Support\Facades\DB;
use App\Classes\Shared\Widgets\Charts\Chart;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use App\Constants;

/**
 * Description of PieChartQueries
 * Contains various methods that return an object similar to the data attribute of a JavaScript Chart from www.chartjs.org
 * See also dashboard.run.js
 *
 * @author josemiguel
 */
class PieChartQueries extends Chart {

    public static function query($template_wheres = null) {
        if (!$template_wheres) {
            $template_wheres = [
                ['is_pledge', '=', false]
            ];
        }

        $query = TransactionSplit::whereHas('transaction', function($query) use ($template_wheres) {
                    $query->whereHas('template', function($q) use ($template_wheres) {
                        $q->where($template_wheres);
                    });
                })->join('transactions', 'transaction_splits.transaction_id', '=', 'transactions.id')
                ->where([
            ['transactions.tenant_id', '=', auth()->user()->tenant->id],
            ['transactions.deleted_at', '=', null]
        ]);

        return $query;
    }

    public static function recurringVsOneTimeDonationsRunQuery($from, $to) {
        $recurringDonations = self::query([
                            ['is_recurring', '=', true],
                            ['is_pledge', '=', false]
                        ])
                        ->where([
                            ['transactions.status', '=', 'complete'],
                        ])
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();

        $notInIds = array_pluck($recurringDonations, 'id');
        $oneTimeDonations = self::query([
                            ['is_recurring', '=', false],
                            ['is_pledge', '=', false]
                        ])
                        ->where([
                            ['transactions.status', '=', 'complete'],
                        ])
                        ->whereNotIn('transaction_splits.id', $notInIds)
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();

        $labels = ['Recurring Donations', 'One Time Donations'];
        $serie = [$recurringDonations->count(), $oneTimeDonations->count()];
        $colors = [array_get(Constants::CHARTS, 'COLORS.RGB.GREEN'), array_get(Constants::CHARTS, 'COLORS.RGB.BLUE')];
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];
        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        return $chart;
    }

    public static function statusDonationsRunQuery($from, $to) {
        $select = [
            DB::raw("transactions.status"),
            DB::raw("COUNT(transaction_splits.id) as total")
        ];
        $donations = self::query()
                ->select($select)
                ->where([
                    ['status', '!=', 'stub']
                ])
                ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
                ->groupBy('transactions.status')
                ->get();

        $labels = $donations->map(function($item) {
            return title_case(array_get($item, 'status'));
        }, []);

        $serie = self::serialize($donations, 'total');
        $colors = [
            array_get(Constants::CHARTS, 'COLORS.RGB.BLUE'),
            array_get(Constants::CHARTS, 'COLORS.RGB.RED'),
            array_get(Constants::CHARTS, 'COLORS.RGB.YELLOW'),
            array_get(Constants::CHARTS, 'COLORS.RGB.PURPLE')
        ];

        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        return $chart;
    }

    public static function creditCardVsAchPaymentsRunQuery($from, $to) {
        $cc = self::query()
                        ->join('payment_options', 'transactions.payment_option_id', '=', 'payment_options.id')
                        ->where([
                            ['transactions.status', '=', 'complete'],
                            ['payment_options.category', '=', 'cc']
                        ])
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();
        
        $ach = self::query()
                        ->join('payment_options', 'transactions.payment_option_id', '=', 'payment_options.id')
                        ->where([
                            ['transactions.status', '=', 'complete'],
                            ['payment_options.category', '=', 'ach']
                        ])
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();

        $labels = ['Credit Card', 'ACH Payments'];
        $serie = [$cc->count(), $ach->count()];
        $colors = [array_get(Constants::CHARTS, 'COLORS.RGB.GREEN'), array_get(Constants::CHARTS, 'COLORS.RGB.BLUE')];
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        
        return $chart;
    }
    
    public static function deviceCategoryRunQuery($from, $to) {
        $select = [
            DB::raw('COUNT(transactions.device_category) as total'),
            DB::raw('transactions.device_category')
        ];
        $query = self::query()
                ->select($select)
                ->where([
                            ['transactions.status', '=', 'complete']
                        ])
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
                ->groupBy('transactions.device_category')->get();
        
        $labels = $query->map(function($item){
            return title_case(array_get($item, 'device_category'));
        });
        $serie = self::serialize($query, 'total');
        $colors = [];
        for($i = 0; $i < count($labels); $i++){
            $color = array_get(Constants::CHARTS, 'COLORS.DEVICE_CATEGORY.'.$i);
            array_push($colors, $color);
        }
        
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        return $chart;
    }
    
    public static function onlineVsOfflineRunQuery($from, $to) {
        $online = self::query()->whereIn('transactions.channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give'])
        ->where('transactions.status', 'complete')
        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();
        
        $offline = self::query()->whereNotIn('transactions.channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give'])
        ->where('transactions.status', 'complete')
        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])->get();
        
        $labels = ['Online Donations', 'Offline Donations'];
        $serie = [$online->count(), $offline->count()];
        $colors = [array_get(Constants::CHARTS, 'COLORS.RGB.GREEN'), array_get(Constants::CHARTS, 'COLORS.RGB.BLUE')];
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        
        return $chart;
    }
    
    public static function transactionPathRunQuery($from, $to) {
        $select = [
            DB::raw('COUNT(transactions.transaction_path) as total'),
            DB::raw('transactions.transaction_path')
        ];
        $query = self::query()
                ->select($select)
                ->where([
                            ['transactions.status', '=', 'complete']
                        ])
                        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
                ->groupBy('transactions.transaction_path')->get();
        
        $labels = $query->map(function($item){
            return title_case(array_get($item, 'transaction_path'));
        });
        $serie = self::serialize($query, 'total');
        
        $colors = [];
        for($i = 0; $i < count($labels); $i++){
            $color = array_get(Constants::CHARTS, 'COLORS.TRANSACTION_PATH.'.$i);
            array_push($colors, $color);
        }
        
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => $label];
        return $chart;
    }
    
    public static function purposesRunQuery($from, $to) {
        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());
        $transactionsplits = self::query()
        ->join('purposes','purpose_id','=','purposes.id')
        ->selectRaw('SUM(amount) as total, purposes.name as purpose_name')
        ->where('transactions.status','complete')
        ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
        ->groupBy('purpose_id')
        ->orderByRaw('SUM(amount) DESC')
        ->get();
        if (!$transactionsplits->count()) {
            return [
                'label' => $label,
                'labels'=>['N/A'],
                'datasets'=>[ 
                    [
                        'data'=>[0],
                        'backgroundColor'=>[array_get(Constants::CHARTS, "COLORS.RGB.RED")]
                    ]
                ],
            ];
        }
        
        // $colors = [ 'RED', 'ORANGE', 'YELLOW', 'GREEN', 'BLUE', 'PURPLE', 'GREY', ];
        $colors = [ 'RED', 'ORANGE', 'YELLOW', 'GREEN', 'BLUE'];
        $labels = [];
        $data = [];
        for ($i=0; $i < count($colors) && $i < $transactionsplits->count() ; $i++) {
            $labels[] = $transactionsplits[$i]->purpose_name;
            $data[] = $transactionsplits[$i]->total;
            $backgroundColor[] = array_get(Constants::CHARTS, "COLORS.RGB.{$colors[$i]}");
        }
        if ($transactionsplits->count() > count($colors)) {
            $i = count($colors) - 1;
            $labels[$i] = 'Other';
            for ($j=$i+1; $j < $transactionsplits->count(); $j++) {
                $data[$i] += $transactionsplits[$j]->total;
            }
        }
        
        $datasets = [compact('data','backgroundColor')];
        
        return compact('label','labels','datasets');
    }
    

}
