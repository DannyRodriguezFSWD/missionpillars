<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWidgetTypesTablePolymorphicFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('widget_types', function (Blueprint $table) {
            $table->string('relation_id', 255)->nullable();
            $table->string('relation_type', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('widget_types', function (Blueprint $table) {
            $table->dropColumn('relation_id', 'relation_type');
        });
    }
}
