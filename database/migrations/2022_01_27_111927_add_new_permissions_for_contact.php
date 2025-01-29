<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewPermissionsForContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the new permissions
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('contact-timeline', 'Contact Timeline', 'Permission to view, crreate, update contact timeline', now(), now());");
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('contact-notes', 'Contact Notes', 'Permission to view, crreate, update contact notes', now(), now());");
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('contact-background', 'Contact Background', 'Permission to view, crreate, update contact background', now(), now());");
        
        // Attach permissions to organization owner
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('contact-timeline', 'contact-notes', 'contact-background') and r.name = 'organization-owner';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the permissions
        DB::statement("DELETE FROM `permissions` where name = 'contact-timeline'");
        DB::statement("DELETE FROM `permissions` where name = 'contact-notes'");
        DB::statement("DELETE FROM `permissions` where name = 'contact-background'");
    }
}
