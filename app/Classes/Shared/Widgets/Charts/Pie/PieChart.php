<?php
namespace App\Classes\Shared\Widgets\Charts\Pie;

use Carbon\Carbon;
/**
 * Description of PieChart
 *
 * @author josemiguel
 */
class PieChart extends PieChartQueries{
    
    public static function recurringVsOneTimeDonations($awidget) {
        list($from,$to) = self::getDateRange($awidget);
        
        $result = self::recurringVsOneTimeDonationsRunQuery($from, $to);
        return $result;
    }
    
    public static function statusDonations($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::statusDonationsRunQuery($from, $to);
        return $result;
    }
    
    public static function creditCardVsAchPayments($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::creditCardVsAchPaymentsRunQuery($from, $to);
        return $result;
    }
    
    public static function deviceCategory($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::deviceCategoryRunQuery($from, $to);
        return $result;
    }
    
    public static function onlineVsOffline($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::onlineVsOfflineRunQuery($from, $to);
        return $result;
    }
    
    public static function transactionPath($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::transactionPathRunQuery($from, $to);
        return $result;
    }
    
    public static function purposes($awidget) {
        list($from, $to) = self::getDateRange($awidget);
        
        $result = self::purposesRunQuery($from, $to);
        return $result;
    }
    
    protected static function getDateRange($awidget) {
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
        
        return [$from,$to];
    }
}
