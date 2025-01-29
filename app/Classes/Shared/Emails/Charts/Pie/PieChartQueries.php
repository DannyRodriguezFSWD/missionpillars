<?php

namespace App\Classes\Shared\Emails\Charts\Pie;

use App\Classes\Shared\Emails\Charts\Chart;
use Illuminate\Support\Facades\DB;
use App\Constants;

/**
 * Description of PieChartQueries
 *
 * @author josemiguel
 */
class PieChartQueries extends Chart {

    public static function query($template_wheres = null) {
        
    }

    public static function graphRunQuery($email) {
        $result = $email->sent()
                ->select(DB::raw('COUNT(id) as total'), 'status')
                ->groupBy('status')
                ->get();
        
        $labels = $result->map(function($item) {
            return title_case(array_get($item, 'status'));
        }, []);
        
        $colors = [];
        for($i = 0; $i < count($labels); $i++){
            $color = array_get(Constants::CHARTS, 'COLORS.DEVICE_CATEGORY.'.$i);
            if(is_null($color)){
                $color = self::randomHexadecimalColor();
            }
            array_push($colors, $color);
        }
        
        $serie = static::serialize($result, 'total');
        $datasets = [
            ['data' => $serie, 'backgroundColor' => $colors]
        ];
        $chart = ['labels' => $labels, 'datasets' => $datasets, 'label' => array_get($email, 'subject').' Stats'];
        return $chart;
        //dd($result, $labels, $color, $serie, $datasets, $chart);
    }

}
