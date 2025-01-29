<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCustomFieldsAddOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->unsignedInteger('custom_field_section_id')->after('tenant_id')->nullable();
            $table->foreign('custom_field_section_id')->references('id')->on('custom_field_sections')->onUpdate('cascade')->onDelete('set null');
            $table->unsignedInteger('sort')->after('imported')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropForeign('custom_fields_custom_field_section_id_foreign');
            $table->dropColumn(['custom_field_section_id', 'sort']);
        });
    }
}
