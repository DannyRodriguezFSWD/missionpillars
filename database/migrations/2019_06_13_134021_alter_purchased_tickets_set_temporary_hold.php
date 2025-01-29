<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPurchasedTicketsSetTemporaryHold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->timestamp('temporary_hold_ends_at')->nullable()->after('used_at');
            $table->tinyInteger('temporary_hold')->default(0)->after('used_at');
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
            $table->dropColumn(['temporary_hold', 'temporary_hold_ends_at']);
        });
    }
}
