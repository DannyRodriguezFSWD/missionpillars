<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSettingValueTableRecreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setting_values', function (Blueprint $table) {
            $table->dropColumn(['description', 'key', 'input_type', 'input_value_type', 'options', 'custom_value', 'default']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('setting_values', function (Blueprint $table) {
            //
            $table->text('description')->nullable()->after('setting_id');
            $table->string('key', 255)->nullable();
            $table->string('input_type', 255)->nullable();//html inputs
            $table->string('input_value_type', 255)->default('string')->after('input_type');
            $table->longText('options')->nullable();
            $table->longText('custom_value')->nullable()->after('options');
            $table->string('default', 255)->nullable();//default field value
        });
    }
}
