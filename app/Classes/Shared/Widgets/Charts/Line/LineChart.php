<?php

namespace App\Classes\Shared\Widgets\Charts\Line;

use Carbon\Carbon;
use App\Classes\Shared\Widgets\Charts\Line\LineChartQueries;
/**
 * Description of LineChart
 *
 * @author josemiguel
 */
class LineChart extends LineChartQueries{

    public static function averageGiftAmount($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->startOfYear();
            $to = Carbon::now();
        } else if ($period === 'current_month'){
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now();
        }
        else{
            $from = Carbon::createFromFormat('Y-m-d', array_get($params, 'from'));
            $to = Carbon::createFromFormat('Y-m-d', array_get($params, 'to'));
        }
        
        $result = self::averageGiftAmountRunQuery($from, $to, array_get($params, 'include_last_year'));
        return $result;
    }
    
    public static function fundraisingMetrics($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->startOfYear();
            $to = Carbon::now();
        } else if ($period === 'current_month'){
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now();
        }
        else{
            $from = Carbon::createFromFormat('Y-m-d', array_get($params, 'from'));
            $to = Carbon::createFromFormat('Y-m-d', array_get($params, 'to'));
        }
        
        $result = self::fundraisingMetricsRunQuery($from, $to, array_get($params, 'include_last_year', false));
        return $result;
    }

}
