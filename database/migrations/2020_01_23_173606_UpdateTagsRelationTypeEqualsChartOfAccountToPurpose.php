<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Constants;

class UpdateTagsRelationTypeEqualsChartOfAccountToPurpose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::table('tags')->where('relation_type','App\\Models\\ChartOfAccount')
        ->update(['relation_type'=>'App\Models\Purpose']);
        DB::table('tags')->where('name','unrecognizedApp\\Models\\ChartOfAccount')
        ->update(['name'=>'unrecognized Purpose']);
        
        DB::table('folders')
        ->where('id',Constants::TAG_SYSTEM['FOLDERS']['CHART_OF_ACCOUNTS'])
        ->update(['name'=>'Purposes']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::table('folders')
        ->where('id',Constants::TAG_SYSTEM['FOLDERS']['CHART_OF_ACCOUNTS'])
        ->update(['name'=>'Chart of Accounts']);
    }
}
