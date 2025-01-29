<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionRegisterRenameRegisterIdToRegisterSplitId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions_registers', function (Blueprint $table) {
            $table->renameColumn('register_id', 'register_split_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions_registers', function (Blueprint $table) {
            $table->renameColumn('register_split_id', 'register_id');
        });
    }
}
