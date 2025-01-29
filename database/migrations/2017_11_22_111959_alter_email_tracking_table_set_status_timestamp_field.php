<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailTrackingTableSetStatusTimestampField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            $table->timestamp('status_timestamp')->nullable()->after('swift_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            $table->dropColumn('status_timestamp');
        });
    }
}
