<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSmsContentAddColumnSmsPhoneNumberTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->string('sms_phone_number_to')->after('sms_phone_number_from')->nullable();
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
            $table->dropColumn('sms_phone_number_to');
        });
    }
}
