<?php

namespace App\Classes\Shared\Charts;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Contact;
use App\Models\Purpose;
use App\Models\Campaign;
use App\Models\Group;
use App\Models\CalendarEvent;
use App\Models\Form;

/**
 * Description of TotalContactsCharts
 *
 * @author josemiguel
 */
class TotalContactsCharts extends Chart implements QueryChartInterface {

    public static function query($select = null) {
        return $select ? Contact::select($select) : Contact::query();
    }

    public static function period($request) {
        $from = Carbon::now();
        $to = Carbon::now()->endOfDay();
        switch (array_get($request, 'period')) {
            case 'current_week':
                $from->startOfWeek();
                $to->endOfDay();
                $label = 'Current Week';
                break;
            case 'current_month':
                $from->startOfMonth();
                $to->endOfDay();
                $label = array_get($request, 'group_by') === 'months' ? 'Current Month' : 'Current Year';
                break;
            case 'date_range':
                $from = new Carbon(array_get($request, 'from'));
                $to = new Carbon(array_get($request, 'to'));
                $to->endOfDay();
                $label = 'From ' . humanReadableDate($from) . ' to ' . humanReadableDate($to);
                break;
            default :
                $from->startOfYear();
                $to->endOfDay();
                $label = 'Current Year';
                break;
        }
        $period = [
            'from' => $from,
            'to' => $to,
            'label' => $label
        ];
        return $period;
    }

    public static function groupBy($request) {
        switch (array_get($request, 'group_by')) {
            case 'days':
                $groupBy = DB::raw('contacts.created_at');
                break;
            case 'weeks':
                $groupBy = DB::raw('WEEK(contacts.created_at)');
                break;
            case 'years':
                $groupBy = DB::raw('YEAR(contacts.created_at)');
                break;
            default :
                $groupBy = DB::raw('MONTH(contacts.created_at)');
                break;
        }

        return $groupBy;
    }

    public static function select($request, $from, $to, $awidget) {
        $groupBy = self::groupBy($request);

        $select = DB::raw('count(contacts.id) as total, contacts.created_at, WEEK(contacts.created_at) as week, MONTH(contacts.created_at) as month, YEAR(contacts.created_at) as year');
        $contacts = self::query($select);
        $filter = array_get($request, 'filter');

        if (!is_null($filter) && $filter !== 'none') {
            switch ($filter) {
                case 'campaign':
                    $items = Campaign::find(array_get($request, 'campaign'));
                    break;
                case 'group':
                    $items = Group::find(array_get($request, 'group'));
                    break;
                case 'event':
                    $items = CalendarEvent::find(array_get($request, 'event'));
                    break;
                case 'form':
                    $items = Form::find(array_get($request, 'form'));
                    break;
                default :
                    $items = Purpose::find(array_get($request, 'chart_of_account'));
                    break;
            }

            if (!is_null($items)) {
                $tags = [];
                foreach ($items as $item) {
                    array_push($tags, array_get($item, 'tagInstance.id', 0));
                }
                $contacts->whereHas('tags', function($query) use ($tags) {
                    $query->whereIn('id', $tags);
                });
            }
            else{
                array_set($awidget, 'has_data', false);
                array_set($awidget, 'message', 'There is no data to display');
                $awidget->update();
            }
        }

        $contacts->whereBetween('contacts.created_at', [$from, $to]);
        $contacts->groupBy($groupBy);

        return $contacts;
    }

    public static function get($request, $awidget = null) {
        $datasets = [];
        $period = self::period($request);

        $currentYear = self::select($request, array_get($period, 'from'), array_get($period, 'to'), $awidget)->get();
        $labels = collect($currentYear)->map(function($item) use ($request) {
            return array_get($request, 'group_by') === 'days' ? __(monthName($item->created_at->toDateTimeString())) : __(monthName($item->month));
        });

        $label = array_get($period, 'label');
        $serie = self::serialize($currentYear, 'total');
        array_push($datasets, self::LineDataset($label, $serie, false, 'rgba(54,162,235,0.5)'));

        if (array_has($request, 'include_last_year')) {
            array_get($period, 'from')->subYear();
            array_get($period, 'to')->subYear();
            array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label')));
            if (array_get($request, 'period') === 'date_range') {
                array_set($period, 'label', str_replace('Current', 'Last', array_get($period, 'label') . ' (Last Year)'));
            }
            $lastYear = self::select($request, array_get($period, 'from'), array_get($period, 'to'), $awidget)->get();
            $label = array_get($period, 'label');
            $serie = self::serialize($lastYear, 'total');
            array_push($datasets, self::LineDataset($label, $serie, false, 'rgba(255,206,86,0.5)'));
        }

        $chartData = ['labels' => $labels->toArray(), 'datasets' => $datasets];
        return $chartData;
    }

}
