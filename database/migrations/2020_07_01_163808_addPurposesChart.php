<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurposesChart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::insert('INSERT into charts (name,description,type,measurement,calculate,what,period,group_by,filter,created_at,updated_at,slug,category) 
        values (?,?,?,?,?,?,?,?,?,?,?,?,?)',['Amount given to Purposes','Displays a pie chart comparing the amount given to purposes.','pie.metric','$','calculate','giving','current_year','months','none',Carbon::now(),Carbon::now(),'pie_purposes','metric']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::delete('DELETE FROM charts WHERE slug = ?',['pie_purposes']);
    }
}
