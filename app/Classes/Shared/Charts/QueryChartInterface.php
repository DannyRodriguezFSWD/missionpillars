<?php

namespace App\Classes\Shared\Charts;

/**
 *
 * @author josemiguel
 */
interface QueryChartInterface {
    public static function query($select = null);
    public static function period($request);
    public static function groupBy($request);
    public static function select($request, $from, $to, $awidget);
    public static function get($request, $awidget = null);
}
