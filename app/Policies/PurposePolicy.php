<?php

namespace App\Policies;

use App\Models\Purpose;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurposePolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('purposes-create');
    }

    public function show(User $user)
    {
        return $user->can('purposes-view');
    }

    public function update(User $user, Purpose $purpose)
    {
        return $user->can('purposes-update') && $purpose->tenant_id == $user->tenant_id;
    }

    public function delete(User $user, Purpose $purpose)
    {
        return $user->can('purposes-delete') && $purpose->tenant_id == $user->tenant_id;
    }
}
