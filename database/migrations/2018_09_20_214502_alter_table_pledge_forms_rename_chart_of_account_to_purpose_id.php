<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePledgeFormsRenameChartOfAccountToPurposeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pledge_forms', function (Blueprint $table) {
            $table->renameColumn('chart_of_account_id', 'purpose_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pledge_forms', function (Blueprint $table) {
            $table->renameColumn('purpose_id', 'chart_of_account_id');
        });
    }
}
