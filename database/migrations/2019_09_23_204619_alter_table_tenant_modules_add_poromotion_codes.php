<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTenantModulesAddPoromotionCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_modules', function (Blueprint $table) {
            $table->string('promo_code')->nullable();
            $table->double('discount_amount')->nullable();
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
            $table->dropColumn('promo_code');
            $table->dropColumn('discount_amount');
        });
    }
}
