<?php

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'organization-owner',
            'display_name' => 'Organization Owner',
            'description' => 'Default system master role',
            'slug' => 'organization-owner',
        ]);
    }
}
