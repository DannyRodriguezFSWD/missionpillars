<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('tag-create');
    }

    public function update(User $user, Tag $tag)
    {
        return $user->can('tag-update') && $user->tenant_id == $tag->tenant_id;
    }

    public function delete(User $user, Tag $tag)
    {
        return $user->can('tag-delete') && $user->tenant_id == $tag->tenant_id;
    }
}
