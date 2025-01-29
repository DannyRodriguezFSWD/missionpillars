<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SmsIncludeTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_include_tags', function (Blueprint $table) {
            $table->unsignedInteger('sms_content_id')->nullable();
            $table->foreign('sms_content_id')->references('id')->on('sms_content')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_include_tags');
    }
}
