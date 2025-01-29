<?php

use Illuminate\Database\Seeder;

class SetUsaCountryFirst extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->where('iso_3166_3', 'USA')->update(['order' => 1]);
    }
}
