<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsContentTableRenameEmailFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_content', function (Blueprint $table) {
            $table->dropColumn(['send_number_of_emails']);
            $table->integer('send_number_of_messages')->nullable()->after('send_to_all');
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
            $table->dropColumn(['send_number_of_messages']);
            $table->integer('send_number_of_emails')->nullable()->after('send_to_all');
        });
    }
}
