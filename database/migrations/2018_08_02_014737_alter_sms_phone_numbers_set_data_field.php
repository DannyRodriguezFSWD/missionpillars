<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsPhoneNumbersSetDataField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_phone_numbers', function (Blueprint $table) {
            $table->text('data')->nullable()->after('sid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_phone_numbers', function (Blueprint $table) {
            $table->dropColumn(['data']);
        });
    }
}
