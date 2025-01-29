<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionForHelpPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the new permission
        DB::statement("INSERT INTO `permissions` (`name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('view-help', 'View Help', 'Permission to view the help page', now(), now());");
        
        // Attach permission to organization owner and non financial users
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('view-help') and r.name in ('organization-owner', 'non-financial-users');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the permission
        DB::statement("DELETE FROM `permissions` where name = 'view-help'");
    }
}
