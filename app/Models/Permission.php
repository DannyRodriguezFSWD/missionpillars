<?php namespace App\Models;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    public static function groupPermissions($permissions)
    {
        $permissionsGrouped = [];
        
        $groupsAdded = [];
        
        foreach ($permissions as $permission) {
            if (!in_array($permission->group_name, $groupsAdded)) {
                $permissionsGrouped[$permission->group_name] = [];
                $groupsAdded[] = $permission->group_name;
            }
            
            $permissionsGrouped[$permission->group_name][] = $permission;
        }
        
        return $permissionsGrouped;
    }
}