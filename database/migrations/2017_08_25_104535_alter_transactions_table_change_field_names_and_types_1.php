<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionsTableChangeFieldNamesAndTypes1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_recurring_transaction_id_foreign');
            $table->dropColumn(['device_category', 'transaction_path', 'recurring_transaction_id']);
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('device_category', 250)->nullable()->after('failure_message');
            $table->string('transaction_path', 250)->nullable('failure_message');
            $table->text('comment')->nullable('failure_message');
            $table->decimal('amount', 12, 2)->nullable()->change();
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
            //
        });
    }
}
