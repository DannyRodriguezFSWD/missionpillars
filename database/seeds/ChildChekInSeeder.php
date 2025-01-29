<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class ChildChekInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permisions = [
            ['name' => 'child-check-in-view', 'display_name' => 'View Child Checkin menu', 'description' => 'Permission to view Child checkin menu'],
            ['name' => 'child-check-in-create', 'display_name' => 'Create check-ins', 'description' => 'Permission to create check-ins'],
            ['name' => 'child-check-in-update', 'display_name' => 'Update check-ins', 'description' => 'Permission to update check-ins'],
            ['name' => 'child-check-in-delete', 'display_name' => 'Delete check-ins', 'description' => 'Permission to delete check-ins'],
        ];
        $admin = Role::where('name', 'organization-owner')->first();
        foreach ($permisions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            DB::table('permission_role')->insert(['permission_id' => $id, 'role_id' => array_get($admin, 'id')]);
        }
    }
}
