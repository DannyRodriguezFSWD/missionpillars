<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTicketsTableSetAvailability extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_options', function (Blueprint $table) {
            $table->integer('availability')->nullable()->after('price');
            $table->tinyInteger('is_free_ticket')->nullable()->after('price');
            $table->tinyInteger('allow_unlimited_tickets')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_options', function (Blueprint $table) {
            $table->dropColumn(['availability', 'is_free_ticket', 'allow_unlimited_tickets']);
        });
    }
}
