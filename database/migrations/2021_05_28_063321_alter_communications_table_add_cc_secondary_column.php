<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommunicationsTableAddCcSecondaryColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->boolean('cc_secondary')->after('include_transactions')->nullable();
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
            $table->dropColumn('cc_secondary');
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }
}
