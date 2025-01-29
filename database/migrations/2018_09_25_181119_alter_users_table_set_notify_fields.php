<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTableSetNotifyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('notify_to_email')->nullable()->after('email');
            $table->string('notify_to_phone')->nullable()->after('email');
            $table->string('notify_to_phone_numbers')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_to_email', 'notify_to_phone', 'notify_to_phone_numbers']);
        });
    }
}
