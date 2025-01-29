<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertSavedSearchesAndAdvancedSearchRowToNewMenuItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = \Carbon\Carbon::now();
        $new_items = [
            [
                'uri' => '/crm/search/contacts/state',
                'tool_tip' => 'List feature replacement.',
                'end_at' => \Carbon\Carbon::now()->endOfYear(),
                'created_at' => $now
            ],
            [
                'uri' => '/crm/search/contacts',
                'tool_tip' => null,
                'end_at' => \Carbon\Carbon::now()->endOfYear(),
                'created_at' => $now
            ]
        ];
        \Illuminate\Support\Facades\DB::table('new_menu_items')->insert($new_items);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $new_items = ['/crm/search/contacts/state', '/crm/search/contacts'];
        \Illuminate\Support\Facades\DB::table('new_menu_items')->whereIn('uri', $new_items)->delete();
    }
}
