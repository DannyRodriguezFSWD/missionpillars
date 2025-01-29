<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultForHiddenColumnInBankTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `bank_transactions` CHANGE COLUMN `hidden` `hidden` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mapped`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `bank_transactions` CHANGE COLUMN `hidden` `hidden` TINYINT(1) NOT NULL AFTER `mapped`");
    }
}
