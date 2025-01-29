<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailContentToStatementTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('statement_templates')->count() == 0) dd('Stop and run database seeders');
        //
        Schema::table('statement_templates', function ($table){
            $table->longText('email_content')->nullable()->after('content');
            $table->renameColumn('content','print_content');
        });
        DB::statement('UPDATE statement_templates SET email_content = print_content');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statement_templates', function ($table){
            $table->dropColumn('email_content');
            $table->renameColumn('print_content','content');
        });
    }
}
