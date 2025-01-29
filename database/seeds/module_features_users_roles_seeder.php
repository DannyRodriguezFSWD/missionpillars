<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Feature;

class module_features_users_roles_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $crm_users = new Feature();
        array_set($crm_users, 'name', 'crm-users');
        array_set($crm_users, 'display_name', 'Users');
        $crm_users->save();

        $crm_roles = new Feature();
        array_set($crm_roles, 'name', 'crm-roles');
        array_set($crm_roles, 'display_name', 'Roles');
        $crm_roles->save();

        DB::table('module_features')->insert([
            [
                'module_id' => 2,
                'feature_id' => array_get($crm_users, 'id')
            ],
            [
                'module_id' => 2,
                'feature_id' => array_get($crm_roles, 'id')
            ],
        ]);
    }
}
