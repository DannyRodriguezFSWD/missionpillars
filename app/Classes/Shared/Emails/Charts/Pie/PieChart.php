<?php
namespace App\Classes\Shared\Emails\Charts\Pie;

use Carbon\Carbon;
/**
 * Description of PieChart
 *
 * @author josemiguel
 */
class PieChart extends PieChartQueries{
    
    
    
    public static function graph($email) {
        $result = self::graphRunQuery($email);
        return $result;
    }
    
}
