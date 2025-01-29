<?php

use Illuminate\Database\Seeder;

class FormSeederNoneForm extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('forms')->insert([
            'name' => 'None',
            'uuid' => \Ramsey\Uuid\Uuid::uuid1()
        ]);
    }
}
