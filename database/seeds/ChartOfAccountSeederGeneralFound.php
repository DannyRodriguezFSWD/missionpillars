<?php

use Illuminate\Database\Seeder;

class ChartOfAccountSeederGeneralFound extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = DB::table('purposes')->insertGetId([
            'name' => 'General Fund',
            'description' => 'General Fund',
        ]);
        
        DB::table('tags')->insert([
            'name' => 'General Fund',
            'folder_id' => 1,
            'is_system_autogenerated' => true,
            'relation_id' => $id,
            'relation_type' => App\Models\ChartOfAccount::class
        ]);
    }
}
