<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarTemplateTableChangeChartToPurpose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `calendar_event_templates`
        CHANGE COLUMN `chart_of_account_id` `purpose_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `campaign_id`");
        // Schema::table('calendar_event_templates', function (Blueprint $table) {
        //     $table->renameColumn('chart_of_account_id', 'purpose_id');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `calendar_event_templates`
        CHANGE COLUMN `purpose_id` `chart_of_account_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `campaign_id`");
        // Schema::table('calendar_event_templates', function (Blueprint $table) {
        //     $table->renameColumn('purpose_id', 'chart_of_account_id');
        // });
    }
}
