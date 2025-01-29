<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class CampaignsPermissionsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $permisions = [
            ['name' => 'campaign-view', 'display_name' => 'View campaigns', 'description' => 'Permission to view campaigns'],
            ['name' => 'campaign-create', 'display_name' => 'Create campaigns', 'description' => 'Permission to create campaigns'],
            ['name' => 'campaign-update', 'display_name' => 'Update campaigns', 'description' => 'Permission to update campaigns'],
            ['name' => 'campaign-delete', 'display_name' => 'Delete campaigns', 'description' => 'Permission to delete campaigns'],
        ];
        $admin = Role::where('name', 'organization-owner')->first();
        foreach ($permisions as $permission) {
            $id = DB::table('permissions')->insertGetId($permission);
            DB::table('permission_role')->insert(['permission_id' => $id, 'role_id' => array_get($admin, 'id')]);
        }
    }

}
