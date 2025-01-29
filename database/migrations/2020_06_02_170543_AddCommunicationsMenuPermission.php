<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Permission;
use App\Models\Role;

class AddCommunicationsMenuPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration depends on exisitng data, check and fail if not present
        if (!Role::global()->where('name','organization-owner')->first()) {
            dd('Stop and run scripts to populate roles table');
        }
        
        $communicationsmenu = new Permission();
        $communicationsmenu->name = 'communications-menu';
        $communicationsmenu->display_name = 'View Communications menu';
        $communicationsmenu->description = 'Permission to view Communications menu';
        $communicationsmenu->save();
        $orgowner = Role::global()->where('name','organization-owner')->first();
        $orgowner->attachPermission($communicationsmenu);
        
        // Additional fix
        Permission::where('name','transactions-menu')->update([
            'description' => 'Permission to view transactions main menu'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Permission::where('name','communications-menu')->delete();
    }
}
