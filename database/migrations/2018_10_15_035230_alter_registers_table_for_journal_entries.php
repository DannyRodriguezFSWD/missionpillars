<?php

use App\Traits\CustomBlueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRegistersTableForJournalEntries extends Migration
{
    use CustomBlueprint;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registers', function (Blueprint $table) {
            $table->string('register_type')->nullable();
            $table->unsignedInteger('journal_entry_id')->nullable();
            $table->unique(['tenant_id', 'journal_entry_id']);
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
        Schema::table('registers', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'journal_entry_id']);
            $table->dropColumn('register_type');
            $table->dropColumn('journal_entry_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
