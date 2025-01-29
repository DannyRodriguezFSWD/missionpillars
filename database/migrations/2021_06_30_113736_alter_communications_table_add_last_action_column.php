<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommunicationsTableAddLastActionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table){
            $table->string('last_action')->default('email');
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
        Schema::table('communications', function (Blueprint $table){
            $table->dropColumn('last_action');
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }
}
