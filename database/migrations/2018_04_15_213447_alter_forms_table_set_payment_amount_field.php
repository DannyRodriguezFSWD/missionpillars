<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFormsTableSetPaymentAmountField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['allow_prefill_amount_change']);
            
        });
        
        Schema::table('forms', function (Blueprint $table) {
            $table->tinyInteger('dont_allow_amount_change')->default(0);
            $table->decimal('payment_amount', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'dont_allow_amount_change']);
            $table->tinyInteger('allow_prefill_amount_change')->default(0);
        });
    }
}
