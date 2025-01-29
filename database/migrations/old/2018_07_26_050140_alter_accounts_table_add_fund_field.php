<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAccountsTableAddFundField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('account_type')->nullable()->change();
            $table->string('activity')->nullable()->change();
            $table->boolean('status')->nullable()->change();
            $table->boolean('sub_account')->nullable()->change();
            $table->integer('parent_account_id')->nullable()->change();
            $table->unsignedInteger('fund_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('fund_id');
        });
    }
}
