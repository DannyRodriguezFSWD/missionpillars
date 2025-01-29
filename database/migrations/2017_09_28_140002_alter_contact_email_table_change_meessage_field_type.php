<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContactEmailTableChangeMeessageFieldType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_email', function (Blueprint $table) {
            $table->dropColumn(['message']);
        });
        
        Schema::table('contact_email', function (Blueprint $table) {
            $table->text('message')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_email', function (Blueprint $table) {
            //
        });
    }
}
