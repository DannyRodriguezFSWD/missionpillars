<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddressesTableSetPolymorphicField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['group_id', 'contact_id']);
            
            $table->string('relation_type', 255)->nullable()->after('tenant_id');
            $table->unsignedInteger('relation_id')->nullable()->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            //
        });
    }
}
