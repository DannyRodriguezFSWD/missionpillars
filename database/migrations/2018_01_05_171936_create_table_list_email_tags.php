<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableListEmailTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_tags', function (Blueprint $table) {
            $table->unsignedInteger('email_content_id')->nullable();
            $table->foreign('email_content_id')->references('id')->on('email_content')->onUpdate('cascade')->onDelete('cascade');
            
            $table->unsignedInteger('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
            
            $table->string('action', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_tags');
    }
}
