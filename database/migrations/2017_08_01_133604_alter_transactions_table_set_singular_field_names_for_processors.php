<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionsTableSetSingularFieldNamesForProcessors extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_processors_transaction_id');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_processor_transaction_id', 255)->nullable();
            $table->string('payment_processor', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }

}
