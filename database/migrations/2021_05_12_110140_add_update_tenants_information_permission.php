<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateTenantsInformationPermission extends Migration
{
    private $permission = ['name' => 'tenant-update', 'display_name' => 'Update Tenant Information', 'description' => 'Permission to update tenant information'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {
        DB::table('permissions')->insert($this->permission);
        $permission = \App\Models\Permission::where('name', $this->permission['name'])->first();
        $role =\App\Models\Role::where('name' ,'organization-owner')->first();
        if ($role && $permission) $role->attachPermission($permission);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = \App\Models\Permission::where('name', $this->permission['name'])->first();
        $role =\App\Models\Role::where('name' ,'organization-owner')->first();
        if ($role && $permission) $role->detachPermission($permission);
        DB::table('permissions')->where('name',$this->permission['name'])->delete();
    }
}
