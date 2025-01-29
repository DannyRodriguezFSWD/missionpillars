<?php

namespace App\Classes\Shared\Widgets\Charts;

use App\Constants;
/**
 * Description of Chart
 *
 * @author josemiguel
 */
class Chart {

    /**
     * Returns data serialized for charts.js
     * @param Collection $data
     */
    public static function serialize($data, $field = 'id') {
        return array_pluck($data, $field);
    }

    public static function randomColorPart($min = 0, $max = 255, $rgba = true) {
        return $rgba ? mt_rand($min, $max) : str_pad(dechex(mt_rand($min, $max)), 2, '0', STR_PAD_LEFT);
    }
    
    public static function getLabels($query, $by) {
        $labels = collect($query)->map(function($item) use ($by) {
            if ($by === 'days') {
                if (!$item->days) {
                    return __(humanReadableDate($item->transaction_initiated_at, true));
                }
                return __(humanReadableDate(array_get($item, 'days'), true));
            } else if ($by === 'device_category') {
                return title_case($item->device_category);
            } else if ($by === 'transaction_path') {
                return title_case($item->transaction_path);
            } else {
                return __(monthName($item->months));
            }
        });

        return $labels;
    }

    public static function randomHexadecimalColor($rgba = true, $hash = false) {
        if($rgba){
            $r = self::randomColorPart();
            $g = self::randomColorPart();
            $b = self::randomColorPart();
            $a = self::randomColorPart(25, 100)/100;
            return "rgba($r,$g,$b,$a)";
        }
        
        $color = self::randomColorPart() . self::randomColorPart() . self::randomColorPart();
        if ($hash) {
            $color = '#' . $color;
        }
        return $color;
    }
    
    public static function LineDataset($label, $data, $color = null) {
        if (!$color) {
            $color = self::randomHexadecimalColor();
        }
        return [
            'label' => $label,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'pointBackgroundColor' => $color,
            'data' => $data
        ];
    }
    
    public static function BarDataset($label, $data, $randomColor = true, $color = 'rgba(0, 0, 0, 0.5)') {
        return self::LineDataset($label, $data, $randomColor, $color);
    }
    
    public static function PieDataset($labels, $data, $type, $randomColor = true, $color = 'rgba(0, 0, 0, 0.5)') {
        $backgroundColor = [];
        if ($randomColor) {
            foreach ($labels as $label){
                array_push($backgroundColor, self::randomHexadecimalColor());
            }
        }
        else{
            foreach ($labels as $label){
                $color =  $type === 'device' ? 'COLORS.DEVICE_CATEGORY.'.strtolower($label) : 'COLORS.TRANSACTION_PATH.'.strtolower(str_slug($label, '_'));
                array_push($backgroundColor, array_get(Constants::CHARTS, $color));
            }
        }
        
        return [
            'data' => $data,
            'backgroundColor' => $backgroundColor,
        ];
    }

}
