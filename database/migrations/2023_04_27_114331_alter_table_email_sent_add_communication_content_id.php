<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmailSentAddCommunicationContentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_sent', function (Blueprint $table) {
            $table->unsignedInteger('communication_content_id')->after('email_content_id')->nullable();
            $table->foreign('communication_content_id')->references('id')->on('communication_contents')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_sent', function (Blueprint $table) {
            $table->dropForeign('email_sent_communication_content_id_foreign');
            $table->dropColumn('communication_content_id');
        });
    }
}
