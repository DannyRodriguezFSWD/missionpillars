<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewMenuItemWidgetPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authorize user to view /new_menu_items
     * @param  User   $user 
     * @return boolean
     */
    public function show(User $user)
    {
        return $user->is_super_admin;
    }

    public function create(User $user)
    {
        return $user->is_super_admin;
    }

    public function update(User $user)
    {
        return $user->is_super_admin;
    }

    public function delete(User $user)
    {
        return $user->is_super_admin;
    }
}
