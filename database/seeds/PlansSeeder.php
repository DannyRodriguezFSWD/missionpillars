<?php

use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->insert([
            ['name' => 'Basic ChMS', 'app_fee' => 0, 'phone_number_fee' => 0, 'sms_fee' => 0, 'email_fee' => 0],
            ['name' => 'Church/Donor/Marketing management', 'app_fee' => 50, 'phone_number_fee' => 10, 'sms_fee' => 0.02, 'email_fee' => 0.02],
            ['name' => 'Accounting', 'app_fee' => 30, 'phone_number_fee' => 0, 'sms_fee' => 0, 'email_fee' => 0],
        ]);
    }
}
