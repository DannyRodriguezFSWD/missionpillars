<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            ['icon' => 'fa fa-envelope-open', 'name' => 'Reminders', 'class_name' => 'App\Models\Pledge'],
            ['icon' => 'fa fa-bell', 'name' => 'Notifications', 'class_name' => 'App\Models\Pledge'],
        ]);
    }
}
