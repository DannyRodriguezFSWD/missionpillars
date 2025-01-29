<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFormsTableSetPyamentFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->renameColumn('collect_funds', 'accept_payments');
            $table->tinyInteger('allow_prefill_amount_change')->default(0);
            $table->tinyInteger('allow_amount_in_url')->default(0);
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
            $table->dropColumn(['allow_prefill_amount_change', 'allow_amount_in_url']);
            $table->renameColumn('accept_payments','collect_funds');
        });
    }
}
