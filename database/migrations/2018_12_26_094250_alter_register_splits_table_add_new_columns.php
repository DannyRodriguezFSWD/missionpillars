<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRegisterSplitsTableAddNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('register_splits', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->after('account_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('splits_partner_id')->after('contact_id')->nullable();
            $table->index('splits_partner_id');
            $table->string('credit')->after('amount')->nullable();
            $table->string('debit')->after('credit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('register_splits', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
            $table->dropIndex(['splits_partner_id']);
            $table->dropColumn('splits_partner_id');
            $table->dropColumn('credit');
            $table->dropColumn('debit');
        });
        Schema::enableForeignKeyConstraints();
    }
}
