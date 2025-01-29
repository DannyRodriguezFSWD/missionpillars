<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplateSplitsTableSetAmountBillingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_template_splits', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->nullable()->after('chart_of_account_id');
            $table->string('type', 200)->nullable()->after('chart_of_account_id');
            $table->smallInteger('tax_deductible')->default(0)->after('chart_of_account_id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_template_splits', function (Blueprint $table) {
            $table->dropColumn([
                'amount',
                'type',
                'tax_deductible'
            ]);
            $table->dropSoftDeletes();
        });
    }
}
