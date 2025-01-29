<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePermissionRoleTableAssignViewReportsPermissionToAllRoleThatHasSettingsViewPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings_view_permission_id = DB::table('permissions')->where('name', 'settings-view')->first()->id;
        $reports_view_permission_id = DB::table('permissions')->where('name', 'reports-view')->first()->id;
        $pairs = DB::table('permission_role')->where('permission_id', $settings_view_permission_id)->get();
        $pairs->each(function ($pair) use ($reports_view_permission_id) {
            $pair->permission_id = $reports_view_permission_id;
            DB::table('permission_role')->insert((array)$pair);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $reports_view_permission_id = DB::table('permissions')->where('name', 'reports-view')->first()->id;
        DB::table('permission_role')->where('permission_id', $reports_view_permission_id)->delete();
    }
}
