<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCalendarEventTemplatesDropRemindCycle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_templates', function (Blueprint $table) {
            $table->dropColumn(['remind_cycle', 'remind_every']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_event_templates', function (Blueprint $table) {
            $table->string('remind_cycle')->after('remind_manager')->nullable();
            $table->unsignedInteger('remind_every')->after('remind_cycle')->nullable();
        });
    }
}
