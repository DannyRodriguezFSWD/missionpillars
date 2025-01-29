<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableContactsAddFamilyIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedInteger('family_id')->after('type')->nullable();
            $table->foreign('family_id')->references('id')->on('families')->onUpdate('cascade')->onDelete('set null');
            $table->string('family_position', 255)->after('family_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_family_id_foreign');
            $table->dropColumn(['family_id', 'family_position']);
        });
    }
}
