<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            ['name' => 'dashboard-view', 'display_name' => 'View Dashboard', 'description' => 'Permission to view dashboard'],
            
            ['name' => 'contact-profile', 'display_name' => 'View contact profile', 'description' => 'Permission to view contact profile'],
            ['name' => 'contacts-list', 'display_name' => 'View contacts list', 'description' => 'Permission to see contacts list'],
            ['name' => 'contact-create', 'display_name' => 'Create contact', 'description' => 'Permission to create contact'],
            ['name' => 'contact-update', 'display_name' => 'Update contact', 'description' => 'Permission to update contact'],
            ['name' => 'contact-delete', 'display_name' => 'Delete contact', 'description' => 'Permission to delete contact'],
            ['name' => 'contact-view', 'display_name' => 'View contact', 'description' => 'Permission to view contact'],
            
            ['name' => 'transactions-menu', 'display_name' => 'View transactions main menu', 'description' => 'Permission to view transaactions main menu'],
            ['name' => 'transaction-create', 'display_name' => 'Create transaction', 'description' => 'Permission to create transaction'],
            ['name' => 'transaction-update', 'display_name' => 'Update transaction', 'description' => 'Permission to update transaction'],
            ['name' => 'transaction-delete', 'display_name' => 'Delete transaction', 'description' => 'Permission to delete transaction'],
            ['name' => 'transaction-view', 'display_name' => 'View transaction', 'description' => 'Permission to view transaction'],
            
            ['name' => 'pledge-create', 'display_name' => 'Create pledge', 'description' => 'Permission to create pledge'],
            ['name' => 'pledge-update', 'display_name' => 'Update pledge', 'description' => 'Permission to update pledge'],
            ['name' => 'pledge-delete', 'display_name' => 'Delete pledge', 'description' => 'Permission to delete pledge'],
            ['name' => 'pledge-view', 'display_name' => 'View pledge', 'description' => 'Permission to view pledge'],

            ['name' => 'purposes-create', 'display_name' => 'Create purposes', 'description' => 'Permission to create Purpose'],
            ['name' => 'purposes-update', 'display_name' => 'Update purposes', 'description' => 'Permission to update Purpose'],
            ['name' => 'purposes-delete', 'display_name' => 'Delete purposes', 'description' => 'Permission to delete Purpose'],
            ['name' => 'purposes-view', 'display_name' => 'View purposes', 'description' => 'Permission to view Purpose'],
            
            ['name' => 'chart-of-account-create', 'display_name' => 'Create chart-of-account', 'description' => 'Permission to create Purpose'],
            ['name' => 'chart-of-account-update', 'display_name' => 'Update chart-of-account', 'description' => 'Permission to update Purpose'],
            ['name' => 'chart-of-account-delete', 'display_name' => 'Delete chart-of-account', 'description' => 'Permission to delete Purpose'],
            ['name' => 'chart-of-account-view', 'display_name' => 'View chart-of-account', 'description' => 'Permission to view Purpose'],
            
            ['name' => 'group-create', 'display_name' => 'Create group', 'description' => 'Permission to create group'],
            ['name' => 'group-update', 'display_name' => 'Update group', 'description' => 'Permission to update group'],
            ['name' => 'group-delete', 'display_name' => 'Delete group', 'description' => 'Permission to delete group'],
            ['name' => 'group-view', 'display_name' => 'View group', 'description' => 'Permission to view group'],
            
            ['name' => 'list-create', 'display_name' => 'Create list', 'description' => 'Permission to create list'],
            ['name' => 'list-update', 'display_name' => 'Update list', 'description' => 'Permission to update list'],
            ['name' => 'list-delete', 'display_name' => 'Delete list', 'description' => 'Permission to delete list'],
            ['name' => 'list-view', 'display_name' => 'View list', 'description' => 'Permission to view list'],
            
            ['name' => 'events-view', 'display_name' => 'View events main menu', 'description' => 'Permission to view events main menu'],
            ['name' => 'event-create', 'display_name' => 'Create event', 'description' => 'Permission to create event'],
            ['name' => 'event-update', 'display_name' => 'Update event', 'description' => 'Permission to update event'],
            ['name' => 'event-delete', 'display_name' => 'Delete event', 'description' => 'Permission to delete event'],
            ['name' => 'event-view', 'display_name' => 'View event', 'description' => 'Permission to view event'],
            
            ['name' => 'form-create', 'display_name' => 'Create form', 'description' => 'Permission to create form'],
            ['name' => 'form-update', 'display_name' => 'Update form', 'description' => 'Permission to update form'],
            ['name' => 'form-delete', 'display_name' => 'Delete form', 'description' => 'Permission to delete form'],
            ['name' => 'form-view', 'display_name' => 'View form', 'description' => 'Permission to view form'],
            
            ['name' => 'settings-view', 'display_name' => 'View settings main menu', 'description' => 'Permission to view settings main menu'],
            ['name' => 'users-list', 'display_name' => 'View users list', 'description' => 'Permission to see users list'],
            ['name' => 'user-create', 'display_name' => 'Create user', 'description' => 'Permission to create user'],
            ['name' => 'user-update', 'display_name' => 'Update user', 'description' => 'Permission to update user'],
            ['name' => 'user-delete', 'display_name' => 'Delete user', 'description' => 'Permission to delete user'],
            ['name' => 'user-view', 'display_name' => 'View user', 'description' => 'Permission to view user'],
            
            ['name' => 'role-create', 'display_name' => 'Create role', 'description' => 'Permission to create role'],
            ['name' => 'role-update', 'display_name' => 'Update role', 'description' => 'Permission to update role'],
            ['name' => 'role-delete', 'display_name' => 'Delete role', 'description' => 'Permission to delete role'],
            ['name' => 'role-view', 'display_name' => 'View role', 'description' => 'Permission to view role'],
            ['name' => 'role-change', 'display_name' => 'Change role', 'description' => 'Permission to change roles'],
            
            ['name' => 'folder-create', 'display_name' => 'Create folder', 'description' => 'Permission to create folder'],
            ['name' => 'folder-update', 'display_name' => 'Update folder', 'description' => 'Permission to update folder'],
            ['name' => 'folder-delete', 'display_name' => 'Delete folder', 'description' => 'Permission to delete folder'],
            ['name' => 'folder-view', 'display_name' => 'View folder', 'description' => 'Permission to view folder'],
            
            ['name' => 'tag-create', 'display_name' => 'Create tag', 'description' => 'Permission to create tag'],
            ['name' => 'tag-update', 'display_name' => 'Update tag', 'description' => 'Permission to update tag'],
            ['name' => 'tag-delete', 'display_name' => 'Delete tag', 'description' => 'Permission to delete tag'],
            ['name' => 'tag-view', 'display_name' => 'View tag', 'description' => 'Permission to view tag'],
            
            ['name' => 'api-create', 'display_name' => 'Create api', 'description' => 'Permission to create api'],
            ['name' => 'api-update', 'display_name' => 'Update api', 'description' => 'Permission to update api'],
            ['name' => 'api-delete', 'display_name' => 'Delete api', 'description' => 'Permission to delete api'],
            ['name' => 'api-view', 'display_name' => 'View api', 'description' => 'Permission to view api'],
            
            ['name' => 'third-party-apps-create', 'display_name' => 'Create third-party-apps', 'description' => 'Permission to create third-party-apps'],
            ['name' => 'third-party-apps-update', 'display_name' => 'Update third-party-apps', 'description' => 'Permission to update third-party-apps'],
            ['name' => 'third-party-apps-delete', 'display_name' => 'Delete third-party-apps', 'description' => 'Permission to delete third-party-apps'],
            ['name' => 'third-party-apps-view', 'display_name' => 'View third-party-apps', 'description' => 'Permission to view third-party-apps']
            
        ]);
    }
}
