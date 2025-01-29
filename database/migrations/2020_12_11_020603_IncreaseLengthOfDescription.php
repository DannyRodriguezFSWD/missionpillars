<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseLengthOfDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pledge_forms', function (Blueprint $table) {
            // Thanks https://github.com/laravel/framework/issues/21847#issuecomment-643450917
            $table->mediumText('description')->comment(' ')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pledge_forms', function (Blueprint $table) {
            //
            $table->text('description')->comment('')->nullable()->change();
        });
    }
}
