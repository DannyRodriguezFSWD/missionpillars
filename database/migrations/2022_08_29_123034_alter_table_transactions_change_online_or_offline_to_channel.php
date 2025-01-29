<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTransactionsChangeOnlineOrOfflineToChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('online_or_offline')->default(null)->change();
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('online_or_offline', 'channel');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('channel')->default('website')->change();
        });
        
        DB::statement("update transactions set channel = 'website' where channel = 'online'");
        DB::statement("update transactions set channel = 'unknown' where channel = 'offline'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('channel')->default(null)->change();
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('channel', 'online_or_offline');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('online_or_offline')->default('online')->change();
        });
        
        DB::statement("update transactions set online_or_offline = 'online' where channel = 'website'");
        DB::statement("update transactions set online_or_offline = 'offline' where channel not in ('website', 'online')");
    }
}
