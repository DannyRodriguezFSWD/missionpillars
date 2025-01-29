<?php

namespace App\Policies;

use App\Models\CalendarEventTemplateSplit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CalendarEventPolicy
{
    use HandlesAuthorization;


    public function viewAll(User $user)
    {
        return $user->can('events-view'); // note the plural
    }

    public function create(User $user)
    {
        return $user->can('event-create');
    }

    public function show(User $user, $split)
    {
        return $user->can('event-view') && $split->tenant_id == $user->tenant_id;
    }

    public function update(User $user, $split)
    {
        return $user->can('event-update') && $split->tenant_id == $user->tenant_id;
    }

    public function delete(User $user, $split)
    {
        return $user->can('event-delete') && $split->tenant_id == $user->tenant_id;
    }
}
