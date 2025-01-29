<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplatesTableChangeFieldNamesAndTypes1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->dropColumn(['name', 'status', 'cycle_type', 'start_date', 'end_date']);
        });
        
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->string('billing_period', 250)->nullable()->after('number_of_cycles');
            $table->integer('billing_frequency')->nullable()->after('billing_period');
            
            $table->renameColumn('remaining_cycles', 'billing_remaining_cycles');
            $table->renameColumn('number_of_cycles', 'billing_cycles');
            $table->renameColumn('total_failures', 'failures');
            $table->renameColumn('total_successes', 'successes');
        });
        
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->timestamp('billing_end_date')->nullable()->after('successes');
            $table->timestamp('billing_start_date')->nullable()->after('successes');
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
            //
        });
    }
}
