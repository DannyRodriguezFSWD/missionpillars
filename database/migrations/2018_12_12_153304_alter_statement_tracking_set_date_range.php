<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatementTrackingSetDateRange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statement_tracking', function (Blueprint $table) {
            $table->smallInteger('use_date_range')->default(0)->after('print_for');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statement_tracking', function (Blueprint $table) {
            $table->dropColumn(['use_date_range']);
        });
    }
}
