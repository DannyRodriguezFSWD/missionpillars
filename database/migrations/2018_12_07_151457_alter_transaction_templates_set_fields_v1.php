<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplatesSetFieldsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->timestamp('cancellation_datetime')->nullable()->after('billing_end_date');
            $table->timestamp('completion_datetime')->nullable()->after('billing_end_date');
            $table->timestamp('subscription_terminated')->nullable()->after('billing_end_date');
            $table->timestamp('subscription_suspended')->nullable()->after('billing_end_date');
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
            $table->dropColumn(['cancellation_datetime', 'completion_datetime', 'subscription_terminated', 'subscription_suspended']);
        });
    }
}
