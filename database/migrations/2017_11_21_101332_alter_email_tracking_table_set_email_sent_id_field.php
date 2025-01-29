<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailTrackingTableSetEmailSentIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_tracking', function (Blueprint $table) {
            $table->unsignedInteger('email_sent_id')->nullable()->after('tenant_id');
            $table->foreign('email_sent_id')->references('id')->on('email_sent')->onUpdate('cascade')->onDelete('cascade');
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
