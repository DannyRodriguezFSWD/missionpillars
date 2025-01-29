<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertEmailContentToCommunications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('email_content','communications');
        Schema::table('communications', function (Blueprint $table) {
            $table->boolean('include_transactions')->default(false);
            $table->boolean('email_exclude_printed')->default(false);
            $table->boolean('exclude_acknowledged_transactions')->default(false);
            $table->boolean('use_date_range')->default(false);
            $table->date('transaction_start_date')->nullable();
            $table->date('transaction_end_date')->nullable();
        });
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW email_content');
        Schema::table('communications', function (Blueprint $table) {
            $table->dropColumn('include_transactions');
            $table->dropColumn('email_exclude_printed');
            $table->dropColumn('exclude_acknowledged_transactions');
            $table->dropColumn('use_date_range');
            $table->dropColumn('transaction_start_date');
            $table->dropColumn('transaction_end_date');
        });
        Schema::rename('communications','email_content');
    }
}
