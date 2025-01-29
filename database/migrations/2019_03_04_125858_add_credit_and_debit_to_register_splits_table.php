<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreditAndDebitToRegisterSplitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('register_splits', function (Blueprint $table) {
            //$table->string('credit')->nullable();
            //$table->string('debit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('register_splits', function (Blueprint $table) {
            //$table->dropColumn('credit');
            //$table->dropColumn('debit');
        });
    }
}
