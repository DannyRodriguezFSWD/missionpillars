<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavedSearchRelationToLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lists', function (Blueprint $table) {
            //
            $table->unsignedInteger('datatable_state_id')->nullable()
            ->after('tenant_id');
            $table->foreign('datatable_state_id')
            ->references('id')->on('datatable_states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lists', function (Blueprint $table) {
            //
            $table->dropForeign('lists_datatable_state_id_foreign');
            $table->dropColumn('datatable_state_id');
        });
    }
}
