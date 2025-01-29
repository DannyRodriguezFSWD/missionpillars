<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionEditProfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the edit-profile permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `group_name`, `created_at`, `updated_at`) VALUES ('edit-profile', 'Edit Profile', 'Permission to edit own profile', 'Contacts', now(), now());");
        
        // Attach edit-profile permission to organization owner, organization contact and non financial users
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('edit-profile') and r.name in ('organization-owner', 'organization-contact', 'non-financial-users');");

        // Add the view-edit-profile-menu permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `group_name`, `created_at`, `updated_at`) VALUES ('view-edit-profile-menu', 'View Edit Profile Menu', 'Permission to view the Edit Profile side menu', 'Contacts', now(), now());");
        
        // Attach view-edit-profile permission to organization contact
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('view-edit-profile-menu') and r.name in ('organization-contact');");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the permissions
        DB::statement("DELETE FROM `permissions` where name in ('edit-profile', 'view-edit-profile-menu')");
    }
}
