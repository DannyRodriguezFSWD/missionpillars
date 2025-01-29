<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionsForOrganizationContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the new permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('contacts-directory', 'View People Directory', 'Permission to view the people directory', now(), now());");
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('group-signup', 'Self Signup In Groups', 'Permission to sign himself up into groups', now(), now());");
        
        // Attach permissions to organization contact
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('contacts-directory', 'group-view', 'group-signup') and r.name = 'organization-contact';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the permissions
        DB::statement("DELETE FROM `permissions` where name in ('contacts-directory', 'group-signup')");
        DB::statement("DELETE FROM permission_role where permission_id in (select id from permissions where name in ('group-view')) and role_id in (select id from roles where name in ('organization-contact'));");
    }
}
