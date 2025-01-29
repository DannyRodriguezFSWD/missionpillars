<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTasksAddEmailDue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('email_due')->after('completed_at')->nullable();
            $table->boolean('email_due_sent')->after('email_due')->default(0);
            $table->integer('due_number')->after('email_due_sent')->nullable();
            $table->string('due_period')->after('due_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['email_due', 'email_due_sent', 'due_number', 'due_period']);
        });
    }
}
