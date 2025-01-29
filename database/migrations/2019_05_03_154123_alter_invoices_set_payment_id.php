<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInvoicesSetPaymentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_invoices', function (Blueprint $table) {
            $table->text('message')->nullable()->after('paid_at');
            $table->string('payment_id', 255)->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_invoices', function (Blueprint $table) {
            $table->dropColumn(['message', 'payment_id']);
        });
    }
}
