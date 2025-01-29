<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('user-create');
    }

    public function update(User $user, User $user_)
    {
        return (auth()->user()->id == (int)$user_->id) || ($user->can('user-update') && $user->tenant_id == $user_->tenant_id);
    }

    public function delete(User $user, User $user_)
    {
        return ($user->can('user-delete') && $user->tenant_id == $user_->tenant_id) && $user->id != $user_->id; // user can delete own user
    }
}
