<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->can('campaign-create');
    }

    public function view(User $user)
    {
        return $user->can('campaign-view');
    }

    public function show(User $user, Campaign $campaign)
    {
        return $user->can('campaign-view') && $user->tenant_id == $campaign->tenant_id;
    }

    public function update(User $user, Campaign $campaign)
    {
        return $user->can('campaign-update') && $user->tenant_id == $campaign->tenant_id;
    }

    public function delete(User $user, Campaign $campaign)
    {
        return $user->can('campaign-delete') && $user->tenant_id == $campaign->tenant_id;
    }
}
