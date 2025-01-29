<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;

class IncludePreferredFallbackToFirstLastNameInGlobalStatementTemplates2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        DB::statement("UPDATE statement_templates 
            SET 
            email_content = REPLACE(email_content,'[:first-name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),
            print_content = REPLACE(print_content,'[:first-name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),
            updated_at = '" . Carbon::now() . "'
            WHERE tenant_id IS NULL"); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement("UPDATE statement_templates 
            SET 
            email_content = REPLACE(email_content,'Dear [:title:] [:preferred-fallback-to-first-last-name:]','Dear [:first-name:]'),
            print_content = REPLACE(print_content,'Dear [:title:] [:preferred-fallback-to-first-last-name:]','Dear [:first-name:]'),
            updated_at = '" . Carbon::now() . "'
            WHERE tenant_id IS NULL"); 
    }
}
