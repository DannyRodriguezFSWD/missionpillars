<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaggablesTable extends Migration
{
    /**
     * Run the migrations.
     * Creates a taggables table that allows polymorphic relations for models and attributes of models
     * NOTE also see AppServiceProvider https://laravel.com/docs/5.8/eloquent-relationships#custom-polymorphic-types 
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tag_id');
            $table->morphs('taggable'); // creates taggable_id, taggable_type and associated index
            $table->string('key')->nullable()->comment('Optional. If present, allows multiple tags to be stored for multiple \'attributes\' in a single');
            $table->timestamps();
            
            $table->index(['taggable_id','taggable_type','key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taggables');
    }
}
