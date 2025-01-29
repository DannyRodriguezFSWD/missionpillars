<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterErrorLogsTableChangeRequestedUrlLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE error_logs CHANGE COLUMN requested_url requested_url VARCHAR(1000) NULL DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE error_logs CHANGE COLUMN requested_url requested_url VARCHAR(191) NULL DEFAULT NULL');
    }
}
