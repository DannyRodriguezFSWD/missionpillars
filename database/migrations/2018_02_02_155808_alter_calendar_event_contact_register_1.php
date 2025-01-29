<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCalendarEventContactRegister1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_event_contact_register', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unsignedInteger('transaction_id')->nullable()->after('check_in');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_event_contact_register', function (Blueprint $table) {
            $table->dropForeign('calendar_event_contact_register_transaction_id_foreign');
            $table->dropColumn(['id', 'transaction_id']);
        });
    }
}
