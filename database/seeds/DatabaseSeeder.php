<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SQLSeeder::class);
        
        //$this->call(TenantTableSeeder::class);
        
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(FoldersTableSeeder::class);
        $this->call(ContactRolesTable::class);
        $this->call(TagsTableSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(CampaignsTableSeeder::class);
        $this->call(ContactRoleSeeder::class);
        $this->call(SetUsaCountryFirst::class);
        $this->call(ChartOfAccountSeederGeneralFound::class);
        $this->call(FormSeederNoneForm::class);
        $this->call(CampaignsPermissionsSeeder::class);
        $this->call(ChildChekInSeeder::class);
        $this->call(AccountingPermissionSeeder::class);
        $this->call(PlansSeeder::class);
        $this->call(FeaturesSeeder::class);
        $this->call(PlanFeaturesSeeder::class);
    }
}
