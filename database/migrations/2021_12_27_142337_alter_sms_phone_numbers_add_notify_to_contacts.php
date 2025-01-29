<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsPhoneNumbersAddNotifyToContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_phone_numbers', function (Blueprint $table) {
            $table->string('notify_to_contacts', 1000)->nullable()->after('notify_to_email');
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
            $table->dropColumn('notify_to_contacts');
        });
    }
}
