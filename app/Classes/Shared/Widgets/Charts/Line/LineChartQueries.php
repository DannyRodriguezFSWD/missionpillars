<?php

namespace App\Classes\Shared\Widgets\Charts\Line;

use App\Classes\Shared\Widgets\Charts\Chart;
use App\Models\TransactionSplit;
use Illuminate\Support\Facades\DB;
use App\Constants;
use Carbon\Carbon;

/**
 * Description of LineChartQueries
 *
 * @author josemiguel
 */
class LineChartQueries extends Chart {

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

    public static function averageGiftAmountRunQuery($from, $to, $includeLastYear = false) {
        $select = [
            DB::raw("AVG(transaction_splits.amount) AS total"),
            DB::raw("DATE(transactions.transaction_initiated_at) as transaction_initiated_at"),
            DB::raw("MONTH(transactions.transaction_initiated_at) as months")
        ];
        if ($from->diffInMonths($to) <= 0) {
            $groupBy = DB::raw('DATE(transactions.transaction_initiated_at)');
            $by = 'days';
        } else {
            $groupBy = DB::raw('months');
            $by = 'months';
        }
        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());

        $currentYearDonations = self::query()->select($select)->where('status', 'complete')
                ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
                ->groupBy($groupBy)->get();

        $serie = self::serialize($currentYearDonations, 'total');
        $datasets = [];

        $labels = self::getLabels($currentYearDonations, $by);
        $dataset = self::LineDataset($label, $serie, array_get(Constants::CHARTS, 'COLORS.RGBA.BLUE'));
        array_push($datasets, $dataset);
        
        if ($includeLastYear === '1') {
            $start = $from->copy()->subYear();
            $end = $to->copy()->subYear();
            $lastYearDonations = self::query()->select($select)->where('status', 'complete')
                    ->whereBetween('transactions.transaction_initiated_at', [$start, $end])
                    ->groupBy($groupBy)->get();
            
            $label = __('From') . ' ' . humanReadableDate($start->toDateTimeString()) . ', to ' . humanReadableDate($end->toDateTimeString());
            $serie = self::serialize($lastYearDonations, 'total');
            $dataset = self::LineDataset($label, $serie, array_get(Constants::CHARTS, 'COLORS.RGBA.YELLOW'));
            array_push($datasets, $dataset);
        }

        $chart = ['labels' => $labels, 'datasets' => $datasets];
        return $chart;
    }
    
    public static function fundraisingMetricsRunQuery($from, $to, $includeLastYear = false) {
        $select = [
            DB::raw("SUM(transaction_splits.amount) AS total"),
            DB::raw("transactions.transaction_initiated_at as days"),
            DB::raw("MONTH(transactions.transaction_initiated_at) as months")
        ];
        if ($from->diffInMonths($to) <= 0) {
            $groupBy = DB::raw('days');
            $by = 'days';
        } else {
            $groupBy = DB::raw('months');
            $by = 'months';
        }
        $label = __('From') . ' ' . humanReadableDate($from->toDateTimeString()) . ', to ' . humanReadableDate($to->toDateTimeString());

        $currentYearDonations = self::query()->select($select)->where('status', 'complete')
                ->whereBetween('transactions.transaction_initiated_at', [$from, $to])
                ->groupBy($groupBy)->get();

        $serie = self::serialize($currentYearDonations, 'total');
        $datasets = [];

        $labels = self::getLabels($currentYearDonations, $by);
        $dataset = self::LineDataset($label, $serie, array_get(Constants::CHARTS, 'COLORS.RGBA.BLUE'));
        array_push($datasets, $dataset);
        
        if ($includeLastYear === '1') {
            $start = $from->copy()->subYear();
            $end = $to->copy()->subYear();
            $lastYearDonations = self::query()->select($select)->where('status', 'complete')
                    ->whereBetween('transactions.transaction_initiated_at', [$start, $end])
                    ->groupBy($groupBy)->get();
            
            $label = __('From') . ' ' . humanReadableDate($start->toDateTimeString()) . ', to ' . humanReadableDate($end->toDateTimeString());
            $serie = self::serialize($lastYearDonations, 'total');
            $dataset = self::LineDataset($label, $serie, array_get(Constants::CHARTS, 'COLORS.RGBA.YELLOW'));
            array_push($datasets, $dataset);
        }

        $chart = ['labels' => $labels, 'datasets' => $datasets];
        return $chart;
    }

}
