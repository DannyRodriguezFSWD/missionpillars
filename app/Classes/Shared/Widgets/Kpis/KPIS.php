<?php

namespace App\Classes\Shared\Widgets\Kpis;

use Carbon\Carbon;

/**
 * Description of KPIS
 *
 * @author josemiguel
 */
class KPIS extends KPISQueries{
    
    public static function averageGift($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->subYear()->startOfYear();
            $to = Carbon::now();
        } else {
            $from = Carbon::createFromDate($period)->subYear()->startOfYear();
            $to = Carbon::createFromDate($period);
        }
        $kpi = self::averageGiftRunQuery($from, $to);
        return $kpi;
    }
    
    public static function averageAnnualGivingDonor($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->subYear()->startOfYear();
            $to = Carbon::now();
        } else {
            $from = Carbon::createFromDate($period)->subYear()->startOfYear();
            $to = Carbon::createFromDate($period);
        }
        
        $kpi = self::averageAnnualGivingDonorRunQuery($from, $to);
        return $kpi;
    }
    
    public static function donorsInDatabase($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->subYear()->startOfYear();
            $to = Carbon::now()->subYear()->endOfYear();
        } else {
            $from = Carbon::createFromDate($period)->subYear()->startOfYear();
            $to = Carbon::createFromDate($period)->subYear()->endOfYear();
        }
        
        $kpi = self::donorsInDatabaseRunQuery($from, $to);
        return $kpi;
    }
    
    public static function donorsRetentionRate($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->startOfYear();
            $to = Carbon::now()->endOfYear();
        } else {
            $from = Carbon::createFromDate($period)->startOfYear();
            $to = Carbon::createFromDate($period)->endOfYear();
        }
        
        $kpi = self::donorsRetentionRateRunQuery($from, $to);
        return $kpi;
    }
    
    public static function donorAttritionRate($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->startOfYear();
            $to = Carbon::now()->endOfYear();
        } else {
            $from = Carbon::createFromDate($period)->startOfYear();
            $to = Carbon::createFromDate($period)->endOfYear();
        }
        
        $kpi = self::donorAttritionRateRunQuery($from, $to);
        return $kpi;
    }
    
    public static function donorParticipationRate($awidget) {
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        $period = array_get($params, 'period');
        
        if ($period === 'current_year') {
            $from = Carbon::now()->startOfYear();
            $to = Carbon::now()->endOfYear();
        } else {
            $from = Carbon::createFromDate($period)->startOfYear();
            $to = Carbon::createFromDate($period)->endOfYear();
        }
        
        $kpi = self::donorParticipationRateRunQuery($from, $to);
        return $kpi;
    }

}
