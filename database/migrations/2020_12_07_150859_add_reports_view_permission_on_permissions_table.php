<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportsViewPermissionOnPermissionsTable extends Migration
{

    private $permission = ['name' => 'reports-view', 'display_name' => 'View Reports', 'description' => 'Permission to view reports'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert($this->permission);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where('name',$this->permission['name'])->delete();
    }
}
