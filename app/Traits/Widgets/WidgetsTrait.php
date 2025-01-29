<?php

namespace App\Traits\Widgets;

use App\Classes\Shared\Widgets\Kpis\KPIS;
use App\Classes\Shared\Widgets\Charts\Pie\PieChart;
use App\Classes\Shared\Widgets\Charts\Line\LineChart;
use App\Models\WidgetType;
use App\Models\Widget;
use Illuminate\Support\Facades\Crypt;
use App\Models\Chart as Metric;
use App\Models\Calendar;
use App\Constants;
use App\Classes\Shared\Widgets\Donations\Incoming;

/**
 *
 * @author josemiguel
 */
trait WidgetsTrait {

    private function getChartWidget($awidget, $params, $isJSON = false) {
        $type = 'line';
        $chart = null;

        switch (array_get($params, 'metric.type')) {
            case 'online_vs_offline_donations':
                $type = 'pie';
                $chart = PieChart::onlineVsOffline($awidget);
                break;
            case 'device_category_donations':
                $type = 'pie';
                $chart = PieChart::deviceCategory($awidget);
                break;
            case 'transaction_type_donations':
                $type = 'pie';
                $chart = PieChart::transactionPath($awidget);
                break;
            case 'donations_average_current_year':
                $type = 'line';
                $chart = LineChart::averageGiftAmount($awidget);
                break;
            case 'all_donations_current_year':
                $type = 'line';
                $chart = LineChart::fundraisingMetrics($awidget);
                break;
            case 'pie_recurring_vs_one_time_donations':
                $type = 'pie';
                $chart = PieChart::recurringVsOneTimeDonations($awidget);
                break;
            case 'pie_status_donations':
                $type = 'pie';
                $chart = PieChart::statusDonations($awidget);
                break;
            case 'pie_credit_card_vs_ach_payments':
                $type = 'pie';
                $chart = PieChart::creditCardVsAchPayments($awidget);
                break;
            case 'pie_purposes':
                $type = 'pie';
                $chart = PieChart::purposes($awidget);
                break;
        }
        

        $widget = [
            'type' => $type,
            'measurement' => array_get($params, 'measurement'),
            'chart' => $chart
        ];
        if (!$isJSON) {
            array_set($widget, 'widget', $awidget);
        }

        return $widget;
    }

    private function getKPISWidget($awidget, $params) {
        $type = 'kpis';
        $kpis = [];
        if (array_get($params, 'options.checkboxes.average_annual_giving_donor') === 'true') {
            $averageAnnualGivingDonor = KPIS::averageAnnualGivingDonor($awidget);
            if ($averageAnnualGivingDonor) {
                array_push($kpis, $averageAnnualGivingDonor);
            }
        }

        if (array_get($params, 'options.checkboxes.average_gift') === 'true') {
            $averageGift = KPIS::averageGift($awidget);
            if ($averageGift) {
                array_push($kpis, $averageGift);
            }
        }

        if (array_get($params, 'options.checkboxes.donors_in_database') === 'true') {
            $donorsInDatabase = KPIS::donorsInDatabase($awidget);
            if ($donorsInDatabase) {
                array_push($kpis, $donorsInDatabase);
            }
        }

        if (array_get($params, 'options.checkboxes.donors_retention_rate') === 'true') {
            $donorsRetentionRate = KPIS::donorsRetentionRate($awidget);
            if ($donorsRetentionRate) {
                array_push($kpis, $donorsRetentionRate);
            }
        }

        if (array_get($params, 'options.checkboxes.donor_attrition_rate') === 'true') {
            $donorAttritionRate = KPIS::donorAttritionRate($awidget);
            if ($donorAttritionRate) {
                array_push($kpis, $donorAttritionRate);
            }
        }

        if (array_get($params, 'options.checkboxes.donor_participation_rate') === 'true') {
            $donorParticipationRate = KPIS::donorParticipationRate($awidget);
            if ($donorParticipationRate) {
                array_push($kpis, $donorParticipationRate);
            }
        }


        $widget = [
            'type' => $type,
            'name' => array_get($awidget, 'name'),
            'kpis' => $kpis
        ];
        return $widget;
    }

    private function getCalendarWidget($awidget, $params) {
        $calendar = Calendar::with('events')->where('id', array_get($params, 'id'))->first();
        if (!$calendar) {
            $events = new \Illuminate\Database\Eloquent\Collection();
        } else {
            $color = array_get($calendar, 'color', array_get(Constants::CALENDARS, 'DEFAULT_COLOR'));
            $events = $calendar->events->map(function($item) use ($color) {
                $event = [
                    'id' => array_get($item, 'id'),
                    'allDay' => array_get($item, 'is_all_day'),
                    'title' => array_get($item, 'name'),
                    'start' => array_get($item, 'start'),
                    'end' => array_get($item, 'end'),
                    'color' => $color,
                    'description' => array_get($item, 'description'),
                ];

                return $event;
            }, []);
        }


        $widget = [
            'type' => array_get($awidget, 'type'),
            'name' => array_get($awidget, 'name'),
            'events' => $events->toArray()
        ];

        return $widget;
    }
    
    private function getIncomingMoneyWidget($awidget, $params) {
        $incoming = Incoming::run($awidget);
        $widget = [
            'type' => array_get($awidget, 'type'),
            'name' => array_get($awidget, 'name'),
            'incoming' => $incoming
        ];
        
        return $widget;
    }

    public function getWidgetData($awidget, $isJSON = false) {
        $widget = null;
        $params = json_decode(array_get($awidget, 'parameters', '[]'), true);
        if (array_get($awidget, 'type') === 'chart') {
            $widget = $this->getChartWidget($awidget, $params, $isJSON);
        }

        if (array_get($awidget, 'type') === 'kpis') {
            $widget = $this->getKPISWidget($awidget, $params);
        }

        if (array_get($awidget, 'type') === 'calendar') {
            $widget = $this->getCalendarWidget($awidget, $params);
        }
        
        if (array_get($awidget, 'type') === 'incoming-money') {
            $widget = $this->getIncomingMoneyWidget($awidget, $params);
        }

        return $widget;
    }

    public function addWidget($request) {
        if (array_has($request, 'widget')) {
            $id = array_get($request, 'widget');
            $widgetType = WidgetType::findOrFail($id);

            $max = Widget::max('order') ?: 0;
            $max++;

            $widget = mapModel(new Widget(), $widgetType);

            array_set($widget, 'dashboard_id', auth()->user()->tenant->dashboard->id);
            array_set($widget, 'order', $max);

            if (auth()->user()->tenant->widgets()->save($widget)) {
                if (array_get($widget, 'type') === 'chart') {
                    array_set($widget, 'metric', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
                }
                if (array_get($widget, 'type') === 'kpis') {
                    array_set($widget, 'data', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
                }
                if (array_get($widget, 'type') === 'calendar') {
                    $params = json_decode(array_get($widget, 'parameters'), true);
                    $calendar = Calendar::with('events')->first();
                    array_set($params, 'id', (string) array_get($calendar, 'id'));

                    array_set($widget, 'parameters', json_encode($params));
                    $widget->update();

                    array_set($widget, 'data', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', $params);
                }
                if (array_get($widget, 'type') === 'incoming-money') {
                    array_set($widget, 'data', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
                }
                array_set($widget, 'uid', Crypt::encrypt(array_get($widget, 'id')));
                return $widget;
            }
        }
        return null;
    }
    
    public function addMetric($request) {
        if (array_has($request, 'metric')) {
            $id = array_get($request, 'widget');
            $widgetType = WidgetType::findOrFail($id);

            $max = Widget::max('order') ?: 0;
            $max++;

            $widget = mapModel(new Widget(), $widgetType);

            array_set($widget, 'dashboard_id', auth()->user()->tenant->dashboard->id);
            array_set($widget, 'order', $max);

            if (auth()->user()->tenant->widgets()->save($widget)) {
                if (array_get($widget, 'type') === 'chart') {
                    array_set($widget, 'metric', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
                }
                if (array_get($widget, 'type') === 'kpis') {
                    array_set($widget, 'data', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
                }
                if (array_get($widget, 'type') === 'calendar') {
                    $params = json_decode(array_get($widget, 'parameters'), true);
                    $calendar = Calendar::with('events')->first();
                    array_set($params, 'id', (string) array_get($calendar, 'id'));

                    array_set($widget, 'parameters', json_encode($params));
                    $widget->update();

                    array_set($widget, 'data', $this->getWidgetData($widget, true));
                    array_set($widget, 'parameters', $params);
                }
                array_set($widget, 'uid', Crypt::encrypt(array_get($widget, 'id')));
                return $widget;
            }
        }
        return null;
    }

    public function getMetricProperties($request) {
        $metric = Metric::findOrFail(array_get($request, 'metric'));
        $properties = $metric->toArray();
        array_set($properties, 'metric.type', array_get($metric, 'slug'));
        return $properties;
    }

}
