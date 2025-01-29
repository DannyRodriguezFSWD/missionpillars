<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('folder-create');
    }

    public function show(User $user, Folder $folder)
    {
        return $user->tenant_id == $folder->tenant_id || $folder->tenant_id == null;
    }

    public function update(User $user, Folder $folder)
    {
        return $user->can('folder-update') && $user->tenant_id == $folder->tenant_id;
    }

    public function delete(User $user, Folder $folder)
    {
        return $user->can('folder-delete') && $user->tenant_id == $folder->tenant_id;
    }
}
