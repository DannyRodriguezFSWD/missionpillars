<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBankTransactionsAutofillSetPayee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_transactions_autofill', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable()->after('fund_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_transactions_autofill', function (Blueprint $table) {
            $table->dropForeign('bank_transactions_autofill_contact_id_foreign');
            $table->dropColumn(['contact_id']);
        });
    }
}
