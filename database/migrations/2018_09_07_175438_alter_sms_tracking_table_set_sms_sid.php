<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsTrackingTableSetSmsSid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_tracking', function (Blueprint $table) {
            $table->string('sms_sid', 255)->nullable()->after('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_tracking', function (Blueprint $table) {
            $table->dropColumn(['sms_sid']);
        });
    }
}
