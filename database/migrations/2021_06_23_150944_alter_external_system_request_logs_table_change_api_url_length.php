<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExternalSystemRequestLogsTableChangeApiUrlLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE external_system_request_logs CHANGE COLUMN api_url api_url VARCHAR(1000) NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE external_system_request_logs CHANGE COLUMN api_url api_url VARCHAR(191) NULL DEFAULT NULL');
    }
}
