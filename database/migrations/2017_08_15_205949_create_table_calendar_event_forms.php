<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCalendarEventForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_forms', function (Blueprint $table) {
            $table->unsignedInteger('calendar_event_id')->nullable();
            $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('form_id')->nullable();
            $table->foreign('form_id')->references('id')->on('forms')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_forms');
    }
}
