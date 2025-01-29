<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContactsTableAddBloomerangFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('twitter_id')->after('twitter')->nullable();
            $table->string('linkedin_id')->after('twitter_id')->nullable();
            $table->string('facebook_id', 255)->nullable()->change();
            $table->string('type')->after('unsubscribed_from_phones')->default('person');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['twitter_id', 'linkedin_id', 'type']);
        });
    }
}
