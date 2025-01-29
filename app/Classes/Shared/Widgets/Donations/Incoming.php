<?php

namespace App\Classes\Shared\Widgets\Donations;

use App\Classes\Shared\Widgets\Donations\IncomingQueries;
use Carbon\Carbon;

/**
 * Description of Incoming
 *
 * @author josemiguel
 */
class Incoming {
    
    public static function fromOneTime($params) {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();
        return IncomingQueries::fromOneTimeRunQuery($params, $start, $end);
    }
    
    public static function fromPledges($params) {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();
        return IncomingQueries::fromPledgesRunQuery($params, $start, $end);
    }
    
    public static function fromRecurring($params) {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();
        return IncomingQueries::fromRecurringRunQuery($params, $start, $end);
    }
    
    public static function run($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $oneTime = self::fromOneTime($params);
        $pledges = self::fromPledges($params);
        $recurring = self::fromRecurring($params);
        
        $total = $oneTime + $pledges + $recurring;
        $oneTimePercent = $oneTime * 100 / $total;
        $pledgesPercent = $pledges * 100 / $total;
        $recurringPercent = $recurring * 100 / $total;
        
        $totalPercent = $oneTimePercent + $pledgesPercent + $recurringPercent;
        
        $result = [
            'oneTime' => [
                'amount' => $oneTime,
                'percent' => $oneTimePercent
            ],
            'pledges' => [
                'amount' => $pledges,
                'percent' => $pledgesPercent
            ],
            'recurring' => [
                'amount' => $recurring,
                'percent' => $recurringPercent
            ],
            'total' => [
                'amount' => $total,
                'percent' => $totalPercent
            ]
        ];
        
        return $result;
    }
    
}
