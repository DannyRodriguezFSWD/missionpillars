<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterContactRelativesTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_relatives', function (Blueprint $table) {
            $table->renameColumn('relationship', 'relative_relationship');
            $table->string('contact_relationship', 255)->nullable()->after('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_relatives', function (Blueprint $table) {
            $table->renameColumn('relative_relationship', 'relationship');
            $table->dropColumn('contact_relationship');
        });
    }
}
