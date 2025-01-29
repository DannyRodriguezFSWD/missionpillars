<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSettingValuesTableSetDescriptionValueTypeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setting_values', function (Blueprint $table) {
            $table->text('description')->nullable()->after('setting_id');
            $table->string('input_value_type', 255)->default('string')->after('input_type');
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
            $table->dropColumn(['description', 'input_value_type']);
        });
    }
}
