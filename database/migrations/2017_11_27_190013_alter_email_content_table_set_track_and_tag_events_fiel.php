<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailContentTableSetTrackAndTagEventsFiel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_content', function (Blueprint $table) {
            $table->longText('track_and_tag_events')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_content', function (Blueprint $table) {
            $table->dropColumn('track_and_tag_events');
        });
    }
}
