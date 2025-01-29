<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTransactionTemplatesAddCoverTheCostAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->decimal('cover_the_cost_amount', 12, 2)->after('amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->dropColumn('cover_the_cost_amount');
        });
    }
}
