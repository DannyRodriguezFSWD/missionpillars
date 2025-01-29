<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePurchasedTicketsAddCheckinFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->timestamp('checked_in_time')->after('checked_in')->nullable();
            $table->timestamp('checked_out_time')->after('checked_in_time')->nullable();
            $table->boolean('printed_tag')->after('checked_out_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->dropColumn(['checked_in_time', 'checked_out_time', 'printed_tag']);
        });
    }
}
