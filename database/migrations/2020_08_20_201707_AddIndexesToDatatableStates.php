<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToDatatableStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('datatable_states', function (Blueprint $table) {
            //
            $table->index('name');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('datatable_states', function (Blueprint $table) {
            //
            $table->dropIndex('datatable_states_name_index');
            $table->dropForeign('datatable_states_created_by_foreign');
            $table->dropForeign('datatable_states_updated_by_foreign');
        });
    }
}
