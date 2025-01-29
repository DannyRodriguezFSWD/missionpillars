<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventTemplateTableSetAllowReserveTicketsAndAllowAutocheckin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_templates', function (Blueprint $table) {
            $table->tinyInteger('allow_reserve_tickets')->nullable()->after('description');
            $table->tinyInteger('allow_auto_check_in')->nullable()->after('description');
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
            $table->dropColumn(['allow_reserve_tickets', 'allow_auto_check_in']);
        });
    }
}
