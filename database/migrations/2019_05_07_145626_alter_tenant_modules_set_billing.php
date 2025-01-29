<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTenantModulesSetBilling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->timestamp('last_billing_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('start_billing_at')->nullable();
            $table->decimal('contact_fee', 12, 2)->default(0)->after('module_id');
            $table->decimal('email_fee', 12, 2)->default(0)->after('module_id');
            $table->decimal('sms_fee', 12, 2)->default(0)->after('module_id');
            $table->decimal('phone_number_fee', 12, 2)->default(0)->after('module_id');
            $table->decimal('app_fee', 12, 2)->default(0)->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->dropColumn([
                'app_fee', 
                'phone_number_fee', 
                'sms_fee',
                'email_fee',
                'contact_fee',
                'start_billing_at',
                'next_billing_at',
                'last_billing_at',
            ]);
        });
    }
}
