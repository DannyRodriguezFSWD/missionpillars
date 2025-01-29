<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBankAccountsAddImportedAndErrorFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->boolean('imported')->after('limit_balance')->default(0);
            $table->date('start_date')->after('imported')->nullable();
            $table->string('plaid_error_code')->after('start_date')->nullable();
            $table->string('plaid_error_message', 5000)->after('plaid_error_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['imported', 'start_date', 'plaid_error_code', 'plaid_error_message']);
        });
    }
}
