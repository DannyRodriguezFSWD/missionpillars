<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionTemplateSplitsSetContactIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_template_splits', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->nullable()->after('tenant_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_template_splits', function (Blueprint $table) {
            $table->dropColumn(['contact_id']);
        });
    }
}
