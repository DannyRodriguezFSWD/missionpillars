<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommunicationsTableAddIncludePublicLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->boolean('include_public_link')->after('cc_secondary')->nullable();
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
            $table->dropColumn('include_public_link');
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }
}
