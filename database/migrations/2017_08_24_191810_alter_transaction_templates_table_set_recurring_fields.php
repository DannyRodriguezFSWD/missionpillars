<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplatesTableSetRecurringFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE transaction_templates MODIFY COLUMN is_recurring SMALLINT AFTER status");
        DB::statement("ALTER TABLE transaction_templates MODIFY COLUMN is_pledge SMALLINT AFTER is_recurring");
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->string('system_created_by', 255)->nullable()->after('is_pledge');
            $table->timestamp('start_date')->nullable()->after('is_pledge');
            $table->timestamp('end_date')->nullable()->after('is_pledge');
            
            $table->integer('total_successes')->nullable()->after('is_pledge');
            $table->integer('total_failures')->nullable()->after('is_pledge');
            $table->integer('number_of_cycles')->nullable()->after('is_pledge');
            $table->integer('remaining_cycles')->nullable()->after('is_pledge');
            $table->enum('cycle_type', ['weekly', 'bi-weekly', 'monthly'])->nullable()->after('is_pledge');
            
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
            $table->dropColumn([
                'system_created_by', 
                'start_date', 'end_date', 
                'total_successes', 
                'total_failures', 
                'number_of_cycles',
                'remaining_cycles',
                'cycle_type'
            ]);
        });
    }
}
