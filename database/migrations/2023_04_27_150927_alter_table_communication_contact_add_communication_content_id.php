<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCommunicationContactAddCommunicationContentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communication_contact', function (Blueprint $table) {
            $table->unsignedInteger('communication_content_id')->after('batch')->nullable();
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
        Schema::table('communication_contact', function (Blueprint $table) {
            $table->dropForeign('communication_contact_communication_content_id_foreign');
            $table->dropColumn('communication_content_id');
        });
    }
}
