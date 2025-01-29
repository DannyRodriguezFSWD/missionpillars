<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBasicCHMSFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('module_features')->where('module_id',1)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $chms_features = DB::table('features')
        ->select(DB::raw('1 AS module_id'),'id AS feature_id')
        ->whereIn('name',
            [ // features copied from production values as of 11/6/2019
                'crm-contacts',
                'crm-transactions',
                'crm-campaigns',
                'crm-purposes',
            ]
        )->get()->map(function($x){ return (array) $x; })->toArray();
        DB::table('module_features')->insert($chms_features);
    }
}
