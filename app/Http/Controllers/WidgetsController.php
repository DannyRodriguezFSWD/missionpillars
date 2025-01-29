<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Widget;
use App\Http\Requests\Dashboard\Widgets\StoreWidget;
use App\Models\Purpose;
use App\Models\Campaign;
use App\Models\Group;
use App\Traits\Widgets\WidgetsTrait;
use Illuminate\Support\Facades\Crypt;
use App\Models\Chart as Metric;

class WidgetsController extends Controller {

    use WidgetsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $data = [
            'charts' => Purpose::all(),
            'campaigns' => Campaign::where('id', '>', 1)->get(),
            'groups' => Group::all()
        ];
        return view('widgets.builder.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWidget $request) {
        $max = Widget::max('order') ?: 0;
        $max++;
        $widget = new Widget();
        array_set($widget, 'name', array_get($request, 'name'));
        array_set($widget, 'order', $max);
        array_set($widget, 'parameters', json_encode($request->except('_token', 'name')));
        if (auth()->user()->tenant->widgets()->save($widget)) {
            return redirect()->route('dashboard.index')->with('message', __('Widget added successfully.'));
        }
        return redirect()->route('dashboard.index')->with('error', __('An error occurred trying to save widget.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        
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
        $widget = Widget::findOrFail($id);
        if ($request->has('widget')) {
            array_set($widget, 'name', array_get($request, 'widget.name'));
            array_set($widget, 'size', array_get($request, 'widget.size'));
            array_set($widget, 'parameters', json_encode(array_get($request, 'widget.parameters')));
            $widget->update();
            array_set($widget, 'uid', Crypt::encrypt(array_get($widget, 'id')));
            if (array_get($widget, 'type') === 'chart') {
                array_set($widget, 'metric', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
            
            if (array_get($widget, 'type') === 'kpis') {
                array_set($widget, 'data', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
            
            if (array_get($widget, 'type') === 'calendar') {
                array_set($widget, 'data', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
            return response()->json($widget);
        }

        array_set($widget, 'size', array_get($request, 'size'));
        if ($widget->update()) {
            return redirect()->route('dashboard.index')->with('message', __('Widget resized'));
        }
        return redirect()->route('dashboard.index')->with('error', __('Error trying to resize  widget'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        if ($request->has('widget')) {
            $widget = Widget::find($id);
            if ($widget) {
                $widget->delete();
                return response()->json(array_get($request, 'widget'));
            }
            return false;
        }
        //this will execute if its used as form
        Widget::destroy($id);
        return redirect()->route('dashboard.index')->with('message', __('Widget successfully deleted'));
    }

    /**
     * Adds a copy of default widget type
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request) {
        $widget = null;
        if (array_has($request, 'widget')) {
            $widget = $this->addWidget($request);
        }
        
        //should add a chart
        if (array_has($request, 'metric')) {
            $max = Widget::max('order') ?: 0;
            $max++;

            $properties = $this->getMetricProperties($request);
            $widget = mapModel(new Widget(), $properties);
            array_set($widget, 'type', 'chart');
            array_set($widget, 'order', $max);
            array_set($widget, 'size', 'col-sm-6 grid-item-width-6 grid-item-height-5');
            array_set($widget, 'parameters', json_encode($properties));
            array_set($widget, 'dashboard_id', auth()->user()->tenant->dashboard->id);
            auth()->user()->tenant->widgets()->save($widget);

            array_set($widget, 'uid', Crypt::encrypt(array_get($widget, 'id')));
            if (array_get($widget, 'type') === 'chart') {
                array_set($widget, 'metric', $this->getWidgetData($widget, true));
                array_set($widget, 'parameters', json_decode(array_get($widget, 'parameters')));
            }
        }

        if ($widget) {
            return response()->json($widget);
        }
        return false;
    }

    public function getMetrics(Request $request) {
        $type = array_get($request, 'type');
        if ($type === 'line.metric') {
            $metrics = Metric::where([
                        ['type', '=', 'line.metric'],
                        ['category', '=', 'metric']
                    ])->get();
            if ($metrics) {
                $properties = [];
                foreach ($metrics as $metric) {
                    $property = $metric->toArray();
                    array_set($property, 'type', 'line.metric');
                    array_set($property, 'metric.type', array_get($metric, 'slug'));
                    array_push($properties, $property);
                }
            }
            return response()->json($properties);
        } else if ($type === 'pie.metric') {
            $metrics = Metric::where([
                        ['type', '=', 'pie.metric'],
                        ['category', '=', 'metric']
                    ])->get();
            if ($metrics) {
                $properties = [];
                foreach ($metrics as $metric) {
                    $property = $metric->toArray();
                    array_set($property, 'type', 'pie.metric');
                    array_set($property, 'metric.type', array_get($metric, 'slug'));
                    array_push($properties, $property);
                }
            }
            return response()->json($properties);
        } else {
            return response()->json([]);
        }
        return response()->json(false);
    }

}
