<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFormsTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->smallInteger('collect_funds')->default(0);
            $table->smallInteger('show_total')->default(0);
            $table->text('cover')->nullable();
            $table->unsignedInteger('campaign_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->dropColumn('html');
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
            //
        });
    }
}
