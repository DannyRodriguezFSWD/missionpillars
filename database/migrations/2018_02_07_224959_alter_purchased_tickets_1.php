<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchasedTickets1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->unsignedInteger('ticket_option_id')->nullable()->after('calendar_event_contact_register_id');
            $table->foreign('ticket_option_id')->references('id')->on('ticket_options')->onUpdate('cascade')->onDelete('cascade');
            $table->string('ticket_name', 255)->nullable()->after('calendar_event_contact_register_id');
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
            $table->dropForeign('purchased_tickets_ticket_option_id_foreign');
            $table->dropColumn(['ticket_option_id', 'ticket_name']);
        });
    }
}
