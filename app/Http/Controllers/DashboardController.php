<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Widget;
use App\Models\WidgetType;
use App\Models\Chart as Metric;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Widgets\WidgetsTrait;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\Calendar;
use App\Constants;
use Carbon\Carbon;
use App\Classes\MissionPillarsLog;

class DashboardController extends Controller {
    use WidgetsTrait;

    public function dismissAlert(Request $request) {
        if( env('APP_ENVIROMENT') === 'demo' ){
            if( !is_null(session('app_demo_alert')) ){
                session(['app_demo_alert' => false]);
            }
        }

        return redirect()->back();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        if( env('APP_ENVIROMENT') === 'demo' ){
            if( is_null(session('app_demo_alert')) ){
                session(['app_demo_alert' => true]);
            }
        }

        if(!auth()->user()->can('dashboard-view')) {
            if (auth()->user()->can('contacts-directory')) {
                return redirect()->route('contacts.directory');
            } else {
                return redirect(route('contacts.show', auth()->user()->contact->id ?: 0));
            }
        }

        if( is_null(auth()->user()->tenant->dashboard) ){
            auth()->user()->tenant->dashboard()->save(new \App\Models\Dashboard());
            auth()->user()->tenant->load('dashboard');
        }

        // TODO consider moving or at least duplicating this logic when the tenant is created as this check is wasted every time after the very first time the user logs in (unless we hard delete tenants widgets in the DB)
        $hasWidgets = auth()->user()->tenant->dashboard->widgets()->withTrashed()->get()->count();

        if(!$hasWidgets){
            $defaulttypes = WidgetType::whereIn('type',
            [ 'welcome', 'gs-videos', 'gs-links' ])->orderByRaw('type = "welcome"')->get();
            foreach ($defaulttypes as $type) {
                $this->addWidget(['widget' => array_get($type, 'id')]);
            }
        }

        $dbyears = Transaction::select(DB::raw('YEAR(MIN(created_at)) as start, YEAR(MAX(created_at)) as end'))->get()->first();

        $min = array_get($dbyears, 'start', Carbon::now()->year);
        $max = array_get($dbyears, 'end', Carbon::now()->year);

        $years = [];
        for($i = $max; $i >= $min; $i--){
            array_push($years, $i);
        }

        $calendars = Calendar::all();
        if (count($calendars) <= 0) {
            $calendar = new Calendar();
            array_set($calendar, 'name', 'Main Calendar');
            array_set($calendar, 'color', array_get(Constants::CALENDARS, 'DEFAULT_COLOR'));
            array_set($calendar, 'is_system_autogenerated', true);
            auth()->user()->tenant->calendars()->save($calendar);
            $calendars = Calendar::all();
        }

        $data = [
            'widgetTypes' => WidgetType::where('type', '!=','chart')->get(),
            'metrics' => Metric::where('category', 'chart')->get(),
            'years' => $years,
            'calendars' => $calendars
        ];

        return view('dashboard.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $widgets = auth()->user()->tenant->dashboard->widgets()->orderBy('order')->get();
        foreach ($widgets as $widget){
            array_set($widget, 'uid', Crypt::encrypt(array_get($widget, 'id')));
            if(array_get($widget, 'type') === 'chart'){
                array_set($widget, 'metric', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
            if(array_get($widget, 'type') === 'kpis'){
                array_set($widget, 'data', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
            if(array_get($widget, 'type') === 'calendar'){
                array_set($widget, 'data', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }

            if(array_get($widget, 'type') === 'incoming-money'){
                array_set($widget, 'data', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
        }
        return response()->json($widgets);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function reorder(Request $request) {
        if(array_has($request, 'data')){
            $data = array_get($request, 'data');
            $i = 1;
            foreach($data as $record){
                $widget = Widget::findOrFail(array_get($record, 'id'));
                array_set($widget, 'order', $i);
                $widget->update();
                unset($widget);
                $i++;
            }
            return response()->json(true);
        }
        return response()->json(false);
    }


    public function help(Request $request) {
        return view('dashboard.help');
    }

    public function noc2g(Request $request) {
        $link = array_get($request, 'link');
        $data = json_encode(auth()->user());
        MissionPillarsLog::click($link, $data);

        return response()->json(true);
    }

    public function newMpAccount(){
        return view('dashboard.newMpAccount');
    }

}
