<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrintFieldsToCommunications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('communications', function ($table) {
            $table->mediumText('print_content')->nullable();
            $table->boolean('print_limit_contacts')->default(false);
            $table->integer('print_max_contacts')->nullable();
            $table->boolean('print_only_paper_statement_contacts')->default(false);
            $table->boolean('print_exclude_emailed')->default(false);
            $table->boolean('print_exclude_printed')->default(false);
            $table->integer('print_exclude_recent_ndays')->nullable();
            $table->unsignedInteger('old_statement_tracking_id')->nullable();
            $table->string('old_print_for')->nullable();
            
            $table->index(['id','old_statement_tracking_id']);
        });
        
        // Migrate statement_tracking data to communications table
        $print = DB::table('statement_tracking')
        ->select(
            'id AS old_statement_tracking_id',
            'tenant_id',
            'uuid',
            'name AS subject',
            'use_date_range',
            DB::raw('1 AS include_transactions'),
            'start_date AS transaction_start_date',
            'end_date AS transaction_end_date',
            'content AS print_content',
            DB::raw("FALSE AS print_limit_contacts,
            NULL AS print_max_contacts,
            IF(print_for = 'donors_marked_paper_statement', true,false) AS print_only_paper_statement_contacts,
            FALSE AS print_exclude_emailed,
            FALSE AS print_exclude_printed,
            NULL AS print_exclude_recent_ndays"),
            'print_for AS old_print_for',
            DB::raw('created_at, updated_at, deleted_at, created_by, updated_by, created_by_session_id, updated_by_session_id')
            )->get()->map(function($x){ return (array) $x; })->toArray();
        DB::table("communications")->insert($print);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nuke migrated statement_tracking data
        DB::table('communications')->whereNotNull('old_statement_tracking_id')->delete();
        
        // drop 'statement_tracking' columns
        Schema::table('communications', function ($table) {
            $table->dropColumn('print_content');
            $table->dropColumn('print_limit_contacts');
            $table->dropColumn('print_only_paper_statement_contacts');
            $table->dropColumn('print_exclude_emailed');
            $table->dropColumn('print_exclude_printed');
            $table->dropColumn('print_exclude_recent_ndays');
            $table->dropColumn('print_max_contacts');
            $table->dropColumn('old_statement_tracking_id');
            $table->dropColumn('old_print_for');
            
            $table->dropIndex(['id','old_statement_tracking_id']);
        });
    }
}
