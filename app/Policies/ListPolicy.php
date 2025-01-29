<?php

namespace App\Policies;

use App\Models\Lists;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ListPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('list-create');
    }

    public function show(User $user, Lists $lists)
    {
        return $user->can('list-view') && $user->tenant_id == $lists->tenant_id;
    }

    public function update(User $user, Lists $lists)
    {
        return $user->can('list-update') && $user->tenant_id == $lists->tenant_id;
    }

    public function delete(User $user, Lists $lists)
    {
        return $user->can('list-delete') && $user->tenant_id == $lists->tenant_id;
    }
}
