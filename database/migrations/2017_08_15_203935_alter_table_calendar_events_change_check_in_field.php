<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCalendarEventsChangeCheckInField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('check_in');
        });
        
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->enum('check_in', ['Everyone', 'Tags', 'Forms', 'None'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('check_in');
            
        });
        
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('check_in');
            $table->text('check_in')->nullable();
        });
    }
}
