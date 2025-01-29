<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsFromRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registers', function($table) {
            $table->dropForeign('registers_contact_id_foreign');
            $table->dropColumn('contact_id');
            $table->dropForeign('registers_fund_id_foreign');
            $table->dropColumn('fund_id');
            $table->dropColumn('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registers', function($table) {
            $table->unsignedInteger('contact_id')->nullable();
            $table->unsignedInteger('fund_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('fund_id')->references('id')->on('funds')->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
