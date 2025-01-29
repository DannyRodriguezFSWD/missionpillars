<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLogErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_errors', function (Blueprint $table) {
            $table->string('event', 255)->nullable()->after('id');
        });
        
        Schema::table('log_errors', function (Blueprint $table) {
            $table->dropColumn(['id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_errors', function (Blueprint $table) {
            //
        });
    }
}
