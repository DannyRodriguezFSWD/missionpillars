<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventContactCheckin1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_contact_check_in', function (Blueprint $table) {
            $table->unsignedInteger('calendar_event_template_split_id')->nullable();
            $table->foreign('calendar_event_template_split_id', 'fk_checkin_event_split_id')->references('id')->on('calendar_event_template_splits')->onUpdate('cascade')->onDelete('cascade');
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
