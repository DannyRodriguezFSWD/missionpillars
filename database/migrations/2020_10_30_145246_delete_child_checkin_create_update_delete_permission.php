<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteChildCheckinCreateUpdateDeletePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    private $permissions = [
        [
            'name' => 'child-check-in-create',
            'display_name' => 'Create check-ins',
            'description' => 'Permission to create check-ins'
        ], [
            'name' => 'child-check-in-update',
            'display_name' => 'Update check-ins',
            'description' => 'Permission to update check-ins'
        ], [
            'name' => 'child-check-in-delete',
            'display_name' => 'Delete check-ins',
            'description' => 'Permission to delete check-ins'
        ],
    ];

    public function up()
    {
        $names = array_map(function ($permission) {
            return $permission['name'];
        }, $this->permissions);
        DB::table('permissions')->whereIn('name', $names)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->insert($this->permissions);
    }
}
