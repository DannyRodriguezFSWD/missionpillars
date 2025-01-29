<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('form-create');
    }

    public function view(User $user)
    {
        return $user->can('form-view');
    }

    public function show(User $user, Form $form)
    {
        return $user->can('form-view') && $user->tenant_id == $form->tenant_id;;
    }

    public function update(User $user, Form $form)
    {
        return $user->can('form-update') && $user->tenant_id == $form->tenant_id;
    }

    public function delete(User $user, Form $form)
    {
        return $user->can('form-delete') && $user->tenant_id == $form->tenant_id;
    }
}
