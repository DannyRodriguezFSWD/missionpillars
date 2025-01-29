<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFormsAddEmailColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->string('email_type')->default('default');
            $table->string('email_subject')->nullable();
            $table->mediumText('email_content')->nullable();
            $table->mediumText('email_content_json')->nullable();
            $table->string('email_editor_type')->default('tiny');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['email_type', 'email_subject', 'email_content', 'email_content_json', 'email_editor_type']);
        });
    }
}
