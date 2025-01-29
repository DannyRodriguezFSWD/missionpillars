<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventContactCheckin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_contact_check_in', function (Blueprint $table) {
            $table->dropForeign(['calendar_event_id']);
            $table->dropColumn(['calendar_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_event_contact_check_in', function (Blueprint $table) {
            //
        });
    }
}
