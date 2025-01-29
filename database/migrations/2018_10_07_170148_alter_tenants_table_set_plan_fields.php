<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTenantsTableSetPlanFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('start_billing_at')->nullable()->after('id');
            $table->tinyInteger('free_month')->default(0)->after('id');
            $table->decimal('email_fee', 12, 2)->default(0)->after('id');
            $table->decimal('sms_fee', 12, 2)->default(0)->after('id');
            $table->decimal('phone_number_fee', 12, 2)->default(0)->after('id');
            $table->decimal('accounting_app_fee', 12, 2)->default(0)->after('id');
            $table->decimal('chms_app_fee', 12, 2)->default(0)->after('id');

            //$table->unsignedInteger('module_id')->nullable()->after('id');
            //$table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            //$table->dropForeign(['module_id']);
            $table->dropColumn([
                'email_fee',
                'sms_fee',
                'phone_number_fee',
                'chms_app_fee',
                'accounting_app_fee',
                'free_month',
                'start_billing_at'
            ]);
        });
    }
}
