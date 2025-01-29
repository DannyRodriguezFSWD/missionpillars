<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSmsContentSetSettingsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->tinyInteger('send_to_all')->nullable();
            $table->integer('send_number_of_emails')->nullable();
            $table->integer('do_not_send_within_number_of_days')->nullable();
            $table->integer('track_and_tag_events')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->dropColumn(['send_to_all', 'send_number_of_emails', 'do_not_send_within_number_of_days', 'track_and_tag_events']);
        });
    }
}
