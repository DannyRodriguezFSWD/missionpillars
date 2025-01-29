<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchasedTicketsSetFormEntryIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->unsignedInteger('form_entry_id')->nullable();
            $table->foreign('form_entry_id')->references('id')->on('form_entries')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->dropForeign('purchased_tickets_form_entry_id_foreign');
            $table->dropColumn(['form_entry_id']);
        });
    }
}
