<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReactivateOnPaidInvoiceIdToTenantModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            //
            $table->unsignedInteger('reactivate_on_paid_invoice_id')->nullable();
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
            //
            $table->dropColumn('reactivate_on_paid_invoice_id');
        });
    }
}
