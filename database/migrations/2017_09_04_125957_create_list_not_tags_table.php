<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListNotTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_not_tags', function (Blueprint $table) {
            $table->unsignedInteger('list_id')->nullable();
            $table->foreign('list_id')->references('id')->on('lists')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('list_not_tags');
    }
}
