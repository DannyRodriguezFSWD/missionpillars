<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExternalApiRequests2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_api_requests', function (Blueprint $table) {
            $table->unsignedInteger('mailgun_id')->nullable()->after('tenant_id');
            $table->foreign('mailgun_id')->references('id')->on('mailgun')->onUpdate('cascade')->onDelete('cascade');
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
            $table->dropForeign(['mailgun_id']);
            $table->dropColumn(['mailgun_id']);
        });
    }
}
