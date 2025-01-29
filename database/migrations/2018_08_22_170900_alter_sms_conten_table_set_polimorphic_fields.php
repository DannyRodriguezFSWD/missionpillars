<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsContenTableSetPolimorphicFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->string('relation_type', 255)->nullable()->after('send_email_if_not_phone_number');
            $table->integer('relation_id')->nullable()->after('send_email_if_not_phone_number');
            $table->string('queued_by', 255)->nullable()->after('send_email_if_not_phone_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->dropColumn(['queued_by', 'relation_id', 'relation_type']);
        });
    }
}
