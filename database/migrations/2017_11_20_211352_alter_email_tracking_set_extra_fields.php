<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailTrackingSetExtraFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('status');
            $table->string('severety', 255)->nullable()->after('status');
            $table->string('log_level', 255)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            //
        });
    }
}
