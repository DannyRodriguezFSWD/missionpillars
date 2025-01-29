<?php

use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('features')->insert([
            ['name' => 'crm-contacts', 'display_name' => 'Contacts'],
            ['name' => 'crm-transactions', 'display_name' => 'Transactions'],
            ['name' => 'crm-pledges', 'display_name' => 'Pledges'],
            ['name' => 'crm-communications', 'display_name' => 'Communications'],
            ['name' => 'crm-purposes', 'display_name' => 'Purposes'],
            ['name' => 'crm-campaigns', 'display_name' => 'Campaigns'],
            ['name' => 'crm-groups', 'display_name' => 'Groups'],
            ['name' => 'crm-events', 'display_name' => 'Events'],
            ['name' => 'crm-forms', 'display_name' => 'Forms'],
            ['name' => 'crm-tasks', 'display_name' => 'Tasks'],
            ['name' => 'crm-child-checkin', 'display_name' => 'Child Checkin'],
            ['name' => 'accounting-transactions', 'display_name' => 'Transactions'],
            ['name' => 'accounting-journal-entry', 'display_name' => 'Journal Entry'],
            ['name' => 'accounting-accounts', 'display_name' => 'Accounts'],
            ['name' => 'accounting-starting-balances', 'display_name' => 'Starting Balances'],
            ['name' => 'accounting-payroll', 'display_name' => 'Payroll'],
            ['name' => 'accounting-reports', 'display_name' => 'Reports'],
            ['name' => 'accounting-bank-integration', 'display_name' => 'Bank Integration'],
        ]);
    }
}
