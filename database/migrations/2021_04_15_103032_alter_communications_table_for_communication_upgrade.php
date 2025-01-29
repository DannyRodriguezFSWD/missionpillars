<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommunicationsTableForCommunicationUpgrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->string('reply_to')->nullable()->after('from_email');
            $table->mediumText('email_content_json')->nullable()->after('content');
            $table->mediumText('print_content_json')->nullable()->after('print_content');
            $table->string('email_editor_type')->default('tiny')->after('email_content_json');
            $table->string('print_editor_type')->default('tiny')->after('print_content_json');
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
            $table->dropColumn(['reply_to', 'email_content_json', 'print_content_json', 'email_editor_type', 'print_editor_type']);
        });
        
        DB::statement('DROP VIEW email_content');
        DB::statement('CREATE VIEW email_content AS SELECT * FROM communications');
    }
}
