<?php

namespace App\Policies;

use App\Constants;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    const FEATURE = 'crm-roles';

    public function feature()
    {
        return auth()->user()->tenant->can(self::FEATURE);
    }

    public function edit(User $user, Role $role)
    {
        return auth()->user()->can('role-update') && $user->tenant_id == $role->tenant_id;
    }

    public function create()
    {
        return auth()->user()->can('role-create');
    }

    public function update(User $user, Role $role)
    {
        return auth()->user()->can('role-update') && $user->tenant_id == $role->tenant_id;
    }

    public function delete(User $user, Role $role)
    {
        return auth()->user()->can('role-delete') && $user->tenant_id == $role->tenant_id;
    }
}
