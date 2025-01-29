<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnder18Permission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Move contacts-directory permission to own group
        DB::statement("UPDATE `permissions` SET group_name = 'Picture Directory' WHERE name = 'contacts-directory'");
        
        // Add the contacts-view-under-18 permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `group_name`, `created_at`, `updated_at`) VALUES ('contacts-view-under-18', 'View People Under 18', 'Permission to show contacts that are under 18', 'Picture Directory', now(), now());");
        
        // Attach contacts-view-under-18 permission to organization owner
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('contacts-view-under-18') and r.name in ('organization-owner');");

        // Change Groups to Small Groups
        DB::statement("UPDATE `permissions` SET group_name = 'Small Groups' WHERE group_name = 'Groups'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Move contacts-directory permission to Contacts group
        DB::statement("UPDATE `permissions` SET group_name = 'Contacts' WHERE name = 'contacts-directory'");
        
        // Drop the permissions
        DB::statement("DELETE FROM `permissions` where name in ('contacts-view-under-18')");
        
        // Change Small Groups to Groups
        DB::statement("UPDATE `permissions` SET group_name = 'Groups' WHERE group_name = 'Small Groups'");
    }
}
