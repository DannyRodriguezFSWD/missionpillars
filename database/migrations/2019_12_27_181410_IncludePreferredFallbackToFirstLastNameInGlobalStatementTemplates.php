<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;

class IncludePreferredFallbackToFirstLastNameInGlobalStatementTemplates extends Migration
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
            email_content = REPLACE(REPLACE(email_content,'[:name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),'[:first_name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),
            print_content = REPLACE(REPLACE(print_content,'[:name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),'[:first_name:]','[:title:] [:preferred-fallback-to-first-last-name:]'),
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
            email_content = REPLACE(REPLACE(email_content,'Dear [:title:] [:preferred-fallback-to-first-last-name:]','Dear [:first_name:]'),'[:title:] [:preferred-fallback-to-first-last-name:]','[:name:]'),
            print_content = REPLACE(REPLACE(print_content,'Dear [:title:] [:preferred-fallback-to-first-last-name:]','Dear [:first_name:]'),'[:title:] [:preferred-fallback-to-first-last-name:]','[:name:]'),
            updated_at = '" . Carbon::now() . "'
            WHERE tenant_id IS NULL"); 
    }
}
