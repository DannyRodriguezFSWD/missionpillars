<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTagFoldersTableToFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('tag_folders', 'folders');
        Schema::table('folders', function (Blueprint $table) {
            $table->renameColumn('tag_folder_parent_id', 'folder_parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_folders', function (Blueprint $table) {
            //
        });
    }
}
