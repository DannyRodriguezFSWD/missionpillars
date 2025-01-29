<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomHeaderColumnOnFormsTable extends Migration
{
    private $table_name = 'forms';
    private $column_name = 'custom_header';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table_name,function(Blueprint $table){
            $table->text($this->column_name)->nullable()->after('custom_landing_page');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table_name,function(Blueprint $table){
            $table->dropColumn($this->column_name);
        });
    }
}
