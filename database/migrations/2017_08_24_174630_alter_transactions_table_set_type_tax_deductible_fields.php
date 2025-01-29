<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionsTableSetTypeTaxDeductibleFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->string('type', 250)->nullable()->after('amount');
            $table->smallInteger('tax_deductible')->default(0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->dropColumn(['type', 'tax_deductible']);
        });
    }
}
