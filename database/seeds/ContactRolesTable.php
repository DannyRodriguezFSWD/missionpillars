<?php

use Illuminate\Database\Seeder;

class ContactRolesTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'organization-contact',
            'display_name' => 'Organization Contact',
            'description' => 'Default system contact role',
            'slug' => 'organization-contact',
        ]);
    }
}
