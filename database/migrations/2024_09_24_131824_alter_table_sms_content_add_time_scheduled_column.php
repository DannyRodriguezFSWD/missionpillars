<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSmsContentAddTimeScheduledColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->dateTime('time_scheduled')->after('add_unsubscribe_message')->nullable();
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
            $table->dropColumn('time_scheduled');
        });
    }
}
