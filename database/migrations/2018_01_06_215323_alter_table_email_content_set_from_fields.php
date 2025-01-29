<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmailContentSetFromFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_content', function (Blueprint $table) {
            $table->string('from_email', 255)->nullable()->after('list_id');
            $table->string('from_name', 255)->nullable()->after('list_id');
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
            $table->dropColumn(['from_email', 'from_name']);
        });
    }
}
