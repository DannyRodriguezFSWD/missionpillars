<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\WidgetType;
use Carbon\Carbon;

class AddGettingStartedWidgetsToWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('widget_types')->count() == 0) dd('No widget types, run necessary migrations/seeders');
        $now = new Carbon();
        // Add new widget types
        $values = [
            [
                'name' => "Getting Started Videos",
                'description' => "Displays videos designed to help you get started with your ChMS",
                'size' => "col-sm-12 grid-item-width-12 grid-item-height-4",
                'type' => "gs-videos",
            ],
            [
                'name' => "Getting Started Links",
                'description' => "Displays links designed to help you get started with your ChMS",
                'size' => "col-sm-12 grid-item-width-12 grid-item-height-4",
                'type' => "gs-links",
            ],
        ];
        
        DB::table('widget_types')->insert($values);
        
        
        // Make space for new widgets on all dashboards
        DB::table('widgets')->whereNull('deleted_at')->increment('order',2);
        
        // Add new widgets for all tenants
        $videowidgets = DB::table('dashboard AS d')
        ->crossJoin('widget_types AS wt')
        ->select('d.id AS dashboard_id','d.tenant_id','wt.name','wt.description','wt.size','wt.type',DB::raw('1 AS `order`, NOW() AS created_at, NOW() AS updated_at'))->where('type','gs-videos')->get()
        ->map(function($x){ return (array) $x; })->toArray();
        
        $linkwidgets = DB::table('dashboard AS d')
        ->crossJoin('widget_types AS wt')
        ->select('d.id AS dashboard_id','d.tenant_id','wt.name','wt.description','wt.size','wt.type',DB::raw('2 AS `order`, NOW() AS created_at, NOW() AS updated_at'))->where('type','gs-links')->get()
        ->map(function($x){ return (array) $x; })->toArray();
        
        DB::table('widgets')->insert($videowidgets);
        DB::table('widgets')->insert($linkwidgets);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('widgets')->whereIn('type',['gs-videos','gs-links'])->delete();
        DB::table('widget_types')->whereIn('type',['gs-videos','gs-links'])->delete();
    }
}
