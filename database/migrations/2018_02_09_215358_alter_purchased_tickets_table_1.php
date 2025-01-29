<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchasedTicketsTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->smallInteger('checked_in')->default(0)->after('price');
            $table->smallInteger('form_filled')->default(0)->after('price');
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
            $table->dropColumn(['checked_in', 'form_filled']);
        });
    }
}
