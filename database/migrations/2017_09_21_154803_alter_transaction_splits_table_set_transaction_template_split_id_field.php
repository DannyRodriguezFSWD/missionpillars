<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionSplitsTableSetTransactionTemplateSplitIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->unsignedInteger('transaction_template_split_id')->nullable();
            $table->foreign('transaction_template_split_id')->references('id')->on('transaction_template_splits')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->dropForeign('transaction_template_split_id');
            $table->dropColumn('transaction_template_split_id');
        });
    }
}
