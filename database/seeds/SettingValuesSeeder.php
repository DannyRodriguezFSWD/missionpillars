<?php

use Illuminate\Database\Seeder;

class SettingValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting_values')->insert([
            ['setting_id' => 1, 'description' => null, 'key' => 'PLEDGE_EMAIL_REMINDER_FREQUENCY', 'value' => '1', 'input_type' => 'custom', 'input_value_type' => 'custom', 'custom_value' => '{"every_number_of_days": 5,"days_before_promised_pay_date": 20}', 'default' => '1'],
            ['setting_id' => 2, 'description' => 'Send email to donor when made donation to pledge', 'key' => 'PLEDGE_EMAIL_NOTIFICATIONS_DONATION_RECEIVED', 'value' => '1', 'input_type' => 'switch', 'input_value_type' => 'boolean', 'custom_value' => null, 'default' => '1'],
            ['setting_id' => 2, 'description' => 'Send email to donor when new pledge its created', 'key' => 'PLEDGE_EMAIL_NOTIFICATIONS_NEW_PLEDGE_DONOR', 'value' => '1', 'input_type' => 'switch', 'input_value_type' => 'boolean', 'custom_value' => null, 'default' => '1'],
            ['setting_id' => 2, 'description' => 'Send email to receiver when new pledge its created', 'key' => 'PLEDGE_EMAIL_NOTIFICATIONS_NEW_PLEDGE_RECEIVER', 'value' => '1', 'input_type' => 'switch', 'input_value_type' => 'boolean', 'custom_value' => null, 'default' => '1'],
        ]);
    }
}
