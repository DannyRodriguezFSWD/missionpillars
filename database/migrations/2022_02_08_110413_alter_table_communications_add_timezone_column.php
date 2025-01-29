<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCommunicationsAddTimezoneColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->string('timezone')->after('transaction_end_date')->nullable();
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }
}
