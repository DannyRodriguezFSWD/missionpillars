<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailTrackingTableDropEmailIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            $table->dropForeign(['email_id']);
            $table->dropColumn('email_id');
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
            //
        });
    }
}
