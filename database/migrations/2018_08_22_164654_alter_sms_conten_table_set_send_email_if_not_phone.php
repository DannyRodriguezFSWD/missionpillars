<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsContenTableSetSendEmailIfNotPhone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->tinyInteger('send_email_if_not_phone_number')->default(0)->after('content');
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
            $table->dropColumn(['send_email_if_not_phone_number']);
        });
    }
}
