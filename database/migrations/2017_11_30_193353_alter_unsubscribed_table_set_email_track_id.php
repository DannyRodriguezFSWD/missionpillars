<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUnsubscribedTableSetEmailTrackId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unsubscribed', function (Blueprint $table) {
            $table->unsignedInteger('email_tracking_id')->after('email_content_id')->nullable();
            $table->foreign('email_tracking_id')->references('id')->on('email_tracking')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unsubscribed', function (Blueprint $table) {
            $table->dropForeign(['email_track_id']);
            $table->dropColumn('email_track_id');
        });
    }
}
