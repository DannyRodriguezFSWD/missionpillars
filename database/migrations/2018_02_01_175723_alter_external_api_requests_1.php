<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Traits\CustomBlueprint;

class AlterExternalApiRequests1 extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_api_requests', function (Blueprint $table) {
            $table->unsignedInteger('tenant_id')->nullable()->after('id');
            $this->setTenantIdForeignKey($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('external_api_requests', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id']);
        });
    }
}
