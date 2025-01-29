<?php

namespace App\Classes\Shared\Charts;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Constants;

/**
 * Description of LineChart
 *
 * @author josemiguel
 */
class DonationsCharts extends Chart implements QueryChartInterface {

    public static function baseQuery() {
        $transactions = Transaction::whereHas('template', function($query) {
                    $query->where([
                        ['is_pledge', '=', false],
                    ]);
                })
                ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
                ->where([
            ['transactions.status', '=', 'complete'],
            ['transactions.tenant_id', '=', auth()->user()->tenant->id],
            ['transactions.deleted_at', '=', null],
        ]);

        return $transactions;
    }

    public static function query($select = null) {
        if (!$select) {
            $select = DB::raw('sum(transaction_splits.amount) as total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year');
        }
        $transactions = Transaction::whereHas('template', function($query) {
                    $query->where([
                        ['is_pledge', '=', false],
                    ]);
                })
                ->join('transaction_splits', 'transaction_splits.transaction_id', '=', 'transactions.id')
                ->select($select)
                ->where([
            ['transactions.status', '=', 'complete'],
            ['transactions.tenant_id', '=', auth()->user()->tenant->id],
            ['transactions.deleted_at', '=', null],
        ]);
        return $select ? $transactions : null;
    }

    public static function period($request) {
        $from = Carbon::now();
        $to = Carbon::now()->endOfDay();
        switch (array_get($request, 'period')) {
            case 'current_year':
                $from->startOfYear();
                $to->endOfDay();
                $label = 'Current Year';
                break;
            case 'current_month':
                $from->startOfMonth();
                $to->endOfDay();
                $label = array_get($request, 'group_by') === 'months' || array_get($request, 'group_by') === 'days' ? 'Current Month' : 'Current Year';
                break;
            case 'date_range':
                $from = new Carbon(array_get($request, 'from'));
                $to = new Carbon(array_get($request, 'to'));
                $to->endOfDay();
                $label = 'From ' . humanReadableDate($from) . ' to ' . humanReadableDate($to);
                break;
            default :
                $from->startOfYear();
                $to->endOfDay();
                $label = 'Current Year';
                break;
        }
        $period = [
            'from' => $from,
            'to' => $to,
            'label' => $label
        ];
        return $period;
    }

    public static function groupBy($request) {
        switch (array_get($request, 'group_by')) {
            case 'days':
                $groupBy = DB::raw('transactions.transaction_last_updated_at');
                break;
            case 'weeks':
                $groupBy = DB::raw('WEEK(transactions.transaction_last_updated_at)');
                break;
            case 'months':
                $groupBy = DB::raw('MONTH(transactions.transaction_last_updated_at)');
                break;
            case 'years':
                $groupBy = DB::raw('YEAR(transactions.transaction_last_updated_at)');
                break;
            case 'device';
                $groupBy = DB::raw('transactions.device_category');
                break;
            case 'transaction_path';
                $groupBy = DB::raw('transactions.transaction_path');
                break;
            default :
                break;
        }

        return $groupBy;
    }

    public static function select($request, $from, $to, $awidget) {
        $groupBy = self::groupBy($request);

        $select = DB::raw('transactions.device_category, transactions.transaction_path, sum(transaction_splits.amount) as total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year');

        $transactions = self::query($select);
        if (array_get($request, 'filter') === 'chart_of_account') {
            $transactions->whereIn('transaction_splits.purpose_id', array_get($request, 'chart_of_account'));
        } else if (array_get($request, 'filter') === 'campaign') {
            $transactions->whereIn('transaction_splits.campaign_id', array_get($request, 'campaign'));
        } else if (array_get($request, 'filter') === 'group') {
            $transactions->join('group_contact', 'group_contact.contact_id', '=', 'transactions.contact_id')
                    ->join('groups', 'groups.id', '=', 'group_contact.group_id')
                    ->whereIn('groups.id', array_get($request, 'group'));
        }

        $transactions->whereBetween('transaction_last_updated_at', [$from, $to]);
        $transactions->groupBy($groupBy);

        return $transactions;
    }

    private static function getLabels($year, $request) {
        $labels = collect($year)->map(function($item) use ($request) {
            if (array_get($request, 'group_by') === 'days') {
                return __(monthName($item->transaction_last_updated_at));
            } else if (array_get($request, 'group_by') === 'device') {
                return title_case($item->device_category);
            } else if (array_get($request, 'group_by') === 'transaction_path') {
                return title_case($item->transaction_path);
            } else {
                return __(monthName($item->month));
            }
        });

        return $labels;
    }

    public static function pieOnlineVsOffline($request, $awidget) {
        $period = self::period($request);

        //$online = self::baseQuery()->join('payment_options', 'transactions.payment_option_id', '=', 'payment_options.id');
        //$online->where('payment_options.category', 'cc');
        $online = self::baseQuery();
        $online->whereIn('transactions.channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give']);
        $online->whereBetween('transactions.transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
        $online->get();

        //$offline = self::baseQuery()->join('payment_options', 'transactions.payment_option_id', '=', 'payment_options.id');
        //$offline->where('payment_options.category', '!=', 'cc');
        $offline = self::baseQuery();
        $offline->whereNotIn('transactions.channel', ['website', 'ctg_direct', 'ctg_embed', 'ctg_text_link', 'ctg_text_give']);
        $offline->whereBetween('transactions.transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
        $offline->get();


        $labels = ['Online Donations', 'Offline Donations'];
        $serie = [$online->get()->count(), $offline->get()->count()];
        $colors = [array_get(Constants::CHARTS, 'COLORS.RGBA.BLUE'), array_get(Constants::CHARTS, 'COLORS.RGBA.YELLOW')];
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];

        $chart = ['labels' => $labels, 'datasets' => $datasets];
        return $chart;
    }
    
    public static function pieDeviceCategory($request, $awidget) {
        array_set($request, 'group_by', 'device');
        $groupBy = self::groupBy($request);
        $period = self::period($request);
        $select = DB::raw('transactions.device_category, transactions.transaction_path, COUNT(transaction_splits.id) as counter, sum(transaction_splits.amount) as total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year');
        $query = self::baseQuery()->select($select);
        $query->whereBetween('transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
        $query->groupBy($groupBy);
        $year = $query->get();
        
        $labels = self::getLabels($year, $request);
        if(array_get($request, 'measurement') === '$'){
            $serie = self::serialize($year, 'total');
        }
        else if(array_get($request, 'measurement') === '#'){
            $serie = self::serialize($year, 'counter');
        }
        else if(array_get($request, 'measurement') === '%'){
            $serie = self::serialize($year, 'counter');
        }
        
        $datasets = [];
        array_push($datasets, self::PieDataset($labels, $serie, array_get($request, 'group_by'), false, null));
        $chart = ['labels' => $labels, 'datasets' => $datasets];
        return $chart;
    }
    
    public static function pieTransactionPath($request, $awidget) {
        array_set($request, 'group_by', 'transaction_path');
        $groupBy = self::groupBy($request);
        $period = self::period($request);
        $select = DB::raw('transactions.device_category, transactions.transaction_path, COUNT(transaction_splits.id) as counter, sum(transaction_splits.amount) as total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year');
        $query = self::baseQuery()->select($select);
        $query->whereBetween('transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
        $query->groupBy($groupBy);
        $year = $query->get();
        
        $labels = self::getLabels($year, $request);
        if(array_get($request, 'measurement') === '$'){
            $serie = self::serialize($year, 'total');
        }
        else if(array_get($request, 'measurement') === '#'){
            $serie = self::serialize($year, 'counter');
        }
        else if(array_get($request, 'measurement') === '%'){
            $serie = self::serialize($year, 'counter');
        }
        
        $datasets = [];
        array_push($datasets, self::PieDataset($labels, $serie, array_get($request, 'group_by'), false, null));
        $chart = ['labels' => $labels, 'datasets' => $datasets];
        return $chart;
    }
    
    public static function get($request, $awidget = null) {
        $datasets = [];
        $period = self::period($request);
        if(array_get($request, 'period') === 'current_month' || array_get($request, 'period') === 'date_range'){
            array_set($request, 'group_by', 'days');
        }
        $currentYear = self::select($request, array_get($period, 'from'), array_get($period, 'to'), $awidget)->get();
        $labels = self::getLabels($currentYear, $request);

        $label = array_get($period, 'label');
        $serie = self::serialize($currentYear, 'total');

        $groupBy = array_get($request, 'group_by');
        if ($groupBy === 'device' || $groupBy === 'transaction_path') {
            array_push($datasets, self::PieDataset($labels, $serie, $groupBy, false, null));
        } else {
            array_push($datasets, self::LineDataset($label, $serie, false, array_get(Constants::CHARTS, 'COLORS.RGBA.BLUE')));
        }


        if ((bool) array_get($request, 'include_last_year', false)) {
            array_get($period, 'from')->subYear();
            array_get($period, 'to')->subYear();
            if (array_get($request, 'period') === 'current_year') {
                array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label')));
            }
            if (array_get($request, 'period') === 'current_month') {
                array_set($period, 'label', str_replace('Current', '', array_get($period, 'label') . ' (Last Year)'));
            }
            if (array_get($request, 'period') === 'date_range') {
                array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label') . ' (Last Year)'));
            }
            $lastYear = self::select($request, array_get($period, 'from'), array_get($period, 'to'), $awidget)->get();
            $label = array_get($period, 'label');
            $serie = self::serialize($lastYear, 'total');
            array_push($datasets, self::LineDataset($label, $serie, false, array_get(Constants::CHARTS, 'COLORS.RGBA.YELLOW')));
        }

        $chartData = ['labels' => $labels->toArray(), 'datasets' => $datasets];
        return $chartData;
    }
    
    public static function lineAverageGiftAmount($request, $awidget) {
        if(array_get($request, 'period') === 'current_month' || array_get($request, 'period') === 'date_range'){
            array_set($request, 'group_by', 'days');
        }
        $groupBy = self::groupBy($request);
        $period = self::period($request);
        $query = self::baseQuery()->select(DB::raw('AVG(transaction_splits.amount) AS total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year'));
        $query->whereBetween('transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
        $query->groupBy($groupBy);
        $year = $query->get();
        
        $labels = self::getLabels($year, $request);
        $serie = self::serialize($year, 'total');
        $datasets = [];
        array_push($datasets, self::LineDataset(array_get($period, 'label'), $serie, false, array_get(Constants::CHARTS, 'COLORS.RGBA.BLUE')));
        
        
        if ((bool) array_get($request, 'include_last_year', false)) {
            array_get($period, 'from')->subYear();
            array_get($period, 'to')->subYear();
            if (array_get($request, 'period') === 'current_year') {
                array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label')));
            }
            if (array_get($request, 'period') === 'current_month') {
                array_set($period, 'label', str_replace('Current', '', array_get($period, 'label') . ' (Last Year)'));
            }
            if (array_get($request, 'period') === 'date_range') {
                array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label') . ' (Last Year)'));
            }
            $query = self::baseQuery()->select(DB::raw('AVG(transaction_splits.amount) AS total, transactions.transaction_last_updated_at, WEEK(transactions.transaction_last_updated_at) as week, MONTH(transactions.transaction_last_updated_at) as month, YEAR(transactions.transaction_last_updated_at) as year'));
            $query->whereBetween('transaction_last_updated_at', [array_get($period, 'from'), array_get($period, 'to')]);
            $query->groupBy($groupBy);
            $lastYear = $query->get();
            
            $label = array_get($period, 'label');
            $serie = self::serialize($lastYear, 'total');
            array_push($datasets, self::LineDataset($label, $serie, false, array_get(Constants::CHARTS, 'COLORS.RGBA.YELLOW')));
        }
        
        $chart = ['labels' => $labels->toArray(), 'datasets' => $datasets];
        return $chart;
    }

}
