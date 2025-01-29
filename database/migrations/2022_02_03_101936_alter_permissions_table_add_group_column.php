<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionsTableAddGroupColumn extends Migration
{
    /**
     * We will group these permissions
     * @var array 
     */
    private $permissionGroups = [
        'Dashboard' => ['dashboard-view'],
        'Contacts' => ['contacts-list', 'contact-create', 'contact-update', 'contact-delete', 'contact-view', 'contact-timeline', 'contact-notes', 'contact-background', 'contacts-directory'],
        'Transactions' => ['transactions-menu', 'transaction-create', 'transaction-update', 'transaction-delete', 'transaction-view', 'transaction-self'],
        'Pledges' => ['pledge-create', 'pledge-update', 'pledge-delete', 'pledge-view'],
        'Purposes' => ['purposes-create', 'purposes-update', 'purposes-delete', 'purposes-view'],
        'Groups' => ['group-create', 'group-update', 'group-delete', 'group-view', 'group-signup'],
        'Lists' => ['list-create', 'list-update', 'list-delete', 'list-view'],
        'Events' => ['events-view', 'event-create', 'event-update', 'event-delete', 'event-view'],
        'Forms' => ['form-create', 'form-update', 'form-delete', 'form-view'],
        'Settings' => ['settings-view'],
        'Users' => ['users-list', 'user-create', 'user-update', 'user-delete', 'user-view'],
        'Roles' => ['role-create', 'role-update', 'role-delete', 'role-view', 'role-change'],
        'Folders' => ['folder-create', 'folder-update', 'folder-delete', 'folder-view'],
        'Tasg' => ['tag-create', 'tag-update', 'tag-delete', 'tag-view'],
        'API' => ['api-create', 'api-update', 'api-delete', 'api-view'],
        'Third Party Apps' => ['third-party-apps-create', 'third-party-apps-update', 'third-party-apps-delete', 'third-party-apps-view'],
        'Campaigns' => ['campaign-view', 'campaign-create', 'campaign-update', 'campaign-delete'],
        'Child Checkin' => ['child-check-in-view'],
        'Accounting' => ['accounting-create', 'accounting-update', 'accounting-delete', 'accounting-view'],
        'Communications' => ['communications-menu'],
        'Reports' => ['reports-view'],
        'Tenants' => ['tenant-update'],
        'Tasks' => ['tasks-view'],
        'Help' => ['view-help']
    ];
    
    /**
     * We will remove these not used permissions
     * @var array 
     */
    private $notUsedPermissions = [
        'contact-profile'
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('group_name')->after('description')->nullable();
        });
        
        // Drop the permission
        $permissionsToDrop = implode("','", $this->notUsedPermissions);
        DB::statement("DELETE FROM `permissions` where name in ('$permissionsToDrop')");
        
        foreach ($this->permissionGroups as $group => $permissions) {
            $permissionsInGroup = implode("','", $permissions);
            DB::statement("UPDATE permissions SET group_name = '$group' WHERE name IN ('$permissionsInGroup')");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('group_name');
        });
    }
}
