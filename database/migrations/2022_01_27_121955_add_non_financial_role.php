<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNonFinancialRole extends Migration
{
    private $permissions = [
        'contact-profile',
        'contacts-list',
        'contact-create',
        'contact-update',
        'contact-delete',
        'contact-view',
        'list-create',
        'list-update',
        'list-delete',
        'list-view',
        'events-view',
        'event-create',
        'event-update',
        'event-delete',
        'event-view',
        'form-create',
        'form-update',
        'form-delete',
        'form-view',
        'user-create',
        'user-update',
        'user-delete',
        'user-view',
        'folder-create',
        'folder-update',
        'folder-delete',
        'folder-view',
        'tag-create',
        'tag-update',
        'tag-delete',
        'tag-view',
        'child-check-in-view',
        'communications-menu',
        'contact-timeline',
        'contact-notes',
        'contact-background'
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new role
        DB::statement("INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`, `tenant_id`, `slug`, `created_by`, `created_by_session_id`) VALUES (NULL, 'non-financial-users', 'Non-Financial Users', 'Use this group for users and volunteers who use the system but should not see financial data', now(), now(), NULL, 'non-financial-users', NULL, NULL);");
        
        // Attach permissions to new role
        DB::statement("insert into permission_role select p.id, r.id from permissions p join roles r where p.name in ('".implode("','", $this->permissions)."') and r.name = 'non-financial-users';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop new role
        DB::statement("DELETE FROM roles WHERE name = 'non-financial-users';");
    }
}
