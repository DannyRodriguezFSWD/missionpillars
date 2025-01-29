<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsSentSetFromToContactIdsFieds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_sent', function (Blueprint $table) {
            $table->renameColumn('contact_id', 'to_contact_id');
            $table->unsignedInteger('from_contact_id')->nullable()->after('tenant_id');
            $table->foreign('from_contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_sent', function (Blueprint $table) {
            $table->renameColumn('to_contact_id', 'contact_id');
            $table->dropForeign(['from_contact_id']);
            $table->dropColumn(['from_contact_id']);
        });
    }
}
