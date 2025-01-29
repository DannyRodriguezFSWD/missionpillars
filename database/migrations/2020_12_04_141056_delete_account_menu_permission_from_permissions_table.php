<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteAccountMenuPermissionFromPermissionsTable extends Migration
{
    private $permission = [
      'name' => 'accounting-menu',
      'display_name' => 'View accounting main menu',
      'description' => 'Permission to view accounting main menu'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->where('name',$this->permission['name'])->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->insert($this->permission);
    }
}
