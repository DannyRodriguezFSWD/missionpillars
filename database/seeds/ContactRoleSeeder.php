<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class ContactRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = Permission::where('name', 'contact-profile')
                ->orWhere('name', 'contact-update')
                ->orWhere('name', 'contact-view')
                ->orWhere('name', 'user-update')
                ->orWhere('name', 'user-view')
                ->get();
        $role = Role::find(2);
        $role->attachPermissions($permissions);
    }
}
