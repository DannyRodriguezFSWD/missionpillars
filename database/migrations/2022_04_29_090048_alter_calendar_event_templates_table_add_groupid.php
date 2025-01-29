<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventTemplatesTableAddGroupid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_templates', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->after('purpose_id')->nullable();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('set null');
            $table->boolean('remind_manager')->after('group_id')->default(0);
            $table->string('remind_cycle')->after('remind_manager')->nullable();
            $table->unsignedInteger('remind_every')->after('remind_cycle')->nullable();
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
            $table->dropForeign('calendar_event_templates_group_id_foreign');
            $table->dropColumn(['group_id', 'remind_manager', 'remind_cycle', 'remind_every']);
        });
    }
}
