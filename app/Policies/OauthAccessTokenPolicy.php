<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OauthAccessTokenPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('api-create');
    }

    public function show(User $user)
    {
        return $user->can('api-view');
    }

    public function delete(User $user)
    {
        return $user->can('api-delete');
    }
}
