<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarEventContactRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_event_contact_register', function (Blueprint $table) {
            $table->unsignedInteger('calendar_event_id')->nullable();
            $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            
            $table->smallInteger('form_filled')->default(0);
            $table->smallInteger('check_in')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_event_contact_register');
    }
}
