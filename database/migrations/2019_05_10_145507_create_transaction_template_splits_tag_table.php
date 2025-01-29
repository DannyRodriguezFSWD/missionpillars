<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTemplateSplitsTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_transaction_template_split', function (Blueprint $table) {
            $table->unsignedInteger('transaction_template_split_id');
            $table->foreign('transaction_template_split_id', 'tt_split_id_foreign')->references('id')->on('transaction_template_splits')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['transaction_template_split_id', 'tag_id'],'tt_split_id_tag_id_primary');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_transaction_template_split');
    }
}
