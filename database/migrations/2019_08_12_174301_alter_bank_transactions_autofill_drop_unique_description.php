<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBankTransactionsAutofillDropUniqueDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_transactions_autofill', function (Blueprint $table) {
            $table->dropUnique(['short_description']);
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
            $table->unique(['short_description']);
        });
    }
}
