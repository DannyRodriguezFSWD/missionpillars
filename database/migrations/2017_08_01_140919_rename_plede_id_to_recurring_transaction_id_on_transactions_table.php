<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePledeIdToRecurringTransactionIdOnTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['pledge_id']);
            $table->dropColumn('pledge_id');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('recurring_transaction_id')->nullable();
            $table->foreign('recurring_transaction_id')->references('id')->on('recurring_transactions')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['recurring_transaction_id']);
            $table->dropColumn('recurring_transaction_id');
        });
        
    }
}
