<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunicationContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communication_contact', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('communication_id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('batch')->default(1);
        
            $table->timestamps();
        
            $table->foreign('communication_id')->references('id')->on('communications')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
        
        
        // migrate contact_statement_tracking data to new table
        $rows = DB::table('contact_statement_tracking AS cst')
        ->join('communications AS c','cst.statement_tracking_id','=','c.old_statement_tracking_id')
        ->select('c.id AS communication_id', 'cst.contact_id', 'cst.created_at', 'cst.updated_at')
        ->whereNotNull('c.old_statement_tracking_id')->get()->map(function($x){ return (array) $x; })->toArray();
        DB::table('communication_contact')->insert($rows);
        
        
        // rename/deprecate statement_tracking table
        Schema::rename('statement_tracking','zz_statement_tracking');
        DB::statement("ALTER TABLE zz_statement_tracking comment 'Deprecated'");
        DB::statement("CREATE VIEW statement_tracking AS
            SELECT 
            id, tenant_id, uuid, 
            subject AS name, old_print_for AS print_for, use_date_range, 
            transaction_start_date AS start_date, transaction_end_date AS end_date, 
            print_content AS content, 
            created_at, updated_at, deleted_at, created_by, updated_by, created_by_session_id, updated_by_session_id
            FROM communications
            WHERE old_print_for IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW statement_tracking");
        DB::statement("ALTER TABLE zz_statement_tracking comment ''");
        Schema::rename('zz_statement_tracking','statement_tracking');
        
        Schema::dropIfExists('communication_contact');
    }
}
