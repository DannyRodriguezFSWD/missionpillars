<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class AccountingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permisions = [
            ['name' => 'accounting-menu', 'display_name' => 'View accounting main menu', 'description' => 'Permission to view accounting main menu'],
            ['name' => 'accounting-create', 'display_name' => 'Create accounting', 'description' => 'Permission to create accounting'],
            ['name' => 'accounting-update', 'display_name' => 'Update accounting', 'description' => 'Permission to update accounting'],
            ['name' => 'accounting-delete', 'display_name' => 'Delete accounting', 'description' => 'Permission to delete accounting'],
            ['name' => 'accounting-view', 'display_name' => 'View accounting', 'description' => 'Permission to view accounting'],
        ];
        $admin = Role::where('name', 'organization-owner')->first();
        foreach ($permisions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            DB::table('permission_role')->insert(['permission_id' => $id, 'role_id' => array_get($admin, 'id')]);
        }
    }
}
