<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplateTableSetLastRemiderSentAtField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent_at')->nullable()->after('promised_pay_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_sent_at']);
        });
    }
}
