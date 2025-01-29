<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContactEmailTableAddStausAsStringSentAtFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_email', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
        
        Schema::table('contact_email', function (Blueprint $table) {
            $table->string('status')->default('Queued')->after('sent'); //Queued, Sent, error
            $table->string('message', 255)->default('Not sent yet')->change();
            $table->timestamp('sent_at')->nullable()->after('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
