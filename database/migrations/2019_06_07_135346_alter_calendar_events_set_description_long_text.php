<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventsSetDescriptionLongText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `calendar_event_templates` CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL COMMENT ' ' COLLATE 'utf8mb4_unicode_ci' AFTER `end`");
        // Schema::table('calendar_event_templates', function (Blueprint $table) {
        //     $table->longText('description')->nullable()->comment(' ')->change();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `calendar_event_templates` CHANGE COLUMN `description` `description` TEXT NULL DEFAULT NULL COMMENT '' COLLATE 'utf8mb4_unicode_ci' AFTER `end`");
        // Schema::table('calendar_event_templates', function (Blueprint $table) {
        //     $table->text('description')->nullable()->comment('')->change();
        // });
    }
}
