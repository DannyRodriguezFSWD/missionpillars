<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContactDirectoryPermissionForOrganizationOwner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Attach contacts-directory permission to organization owner
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('contacts-directory') and r.name in ('organization-owner');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dettach contacts-directory permission to organization owner
        DB::statement("DELETE FROM permission_role where permission_id in (select id from permissions where name in ('contacts-directory')) and role_id in (select id from roles where name in ('organization-owner'));");
    }
}
