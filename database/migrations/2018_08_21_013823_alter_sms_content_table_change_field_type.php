<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsContentTableChangeFieldType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->dropColumn(['track_and_tag_events']);
        });

        Schema::table('sms_content', function (Blueprint $table) {
            $table->text('track_and_tag_events')->nullable();
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
            $table->dropColumn(['track_and_tag_events']);
        });

        Schema::table('sms_content', function (Blueprint $table) {
            $table->integer('track_and_tag_events')->nullable();
        });
    }
}
