<?php

use Illuminate\Database\Seeder;

class PlanFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('module_features')->insert([
            ['module_id' => 2, 'feature_id' => 1],
            ['module_id' => 2, 'feature_id' => 2],
            ['module_id' => 2, 'feature_id' => 3],
            ['module_id' => 2, 'feature_id' => 4],
            ['module_id' => 2, 'feature_id' => 5],
            ['module_id' => 2, 'feature_id' => 6],
            ['module_id' => 2, 'feature_id' => 7],
            ['module_id' => 2, 'feature_id' => 8],
            ['module_id' => 2, 'feature_id' => 9],
            ['module_id' => 2, 'feature_id' => 10],
            ['module_id' => 2, 'feature_id' => 11],
            ['module_id' => 3, 'feature_id' => 12],
            ['module_id' => 3, 'feature_id' => 13],
            ['module_id' => 3, 'feature_id' => 14],
            ['module_id' => 3, 'feature_id' => 15],
            ['module_id' => 3, 'feature_id' => 16],
            ['module_id' => 3, 'feature_id' => 17],
            ['module_id' => 3, 'feature_id' => 18],
        ]);
        
        $this->call(module_features_users_roles_seeder::class);
    }
}
