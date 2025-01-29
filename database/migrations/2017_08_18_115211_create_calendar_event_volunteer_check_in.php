<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarEventVolunteerCheckIn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_volunteer_check_in', function (Blueprint $table) {
            $table->unsignedInteger('calendar_event_id')->nullable();
            $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_volunteer_check_in');
    }
}
