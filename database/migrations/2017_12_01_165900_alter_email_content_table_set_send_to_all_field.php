<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailContentTableSetSendToAllField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_content', function (Blueprint $table) {
            $table->smallInteger('send_to_all')->default(false)->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_content', function (Blueprint $table) {
            $table->dropColumn('send_to_all');
        });
    }
}
