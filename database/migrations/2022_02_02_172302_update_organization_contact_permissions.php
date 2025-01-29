<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrganizationContactPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Remove these permissions
         * contact-update
         * user-update
         * user-view
         * contact-profile
         * contact-view
         */
        DB::statement("DELETE FROM permission_role where permission_id in (select id from permissions where name in ('contact-update', 'user-update', 'user-view', 'contact-profile', 'contact-view')) and role_id in (select id from roles where name in ('organization-contact'));");
        
        // Add the new permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('transaction-self', 'View Own Transactions', 'Permission to view transactions made', now(), now());");
        
        /**
         * Add these permissions
         * transaction-self
         */
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('transaction-self') and r.name = 'organization-contact';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
         * Add these permissions
         * contact-update
         * user-update
         * user-view
         * contact-profile
         * contact-view
         */
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('contact-update', 'user-update', 'user-view', 'contact-profile', 'contact-view') and r.name = 'organization-contact';");
        
        // Drop the permission
        DB::statement("DELETE FROM `permissions` where name = 'transaction-self'");
    }
}
