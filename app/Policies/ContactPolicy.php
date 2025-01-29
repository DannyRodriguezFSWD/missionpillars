<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAll(User $user)
    {
        return $user->can('contacts-list');
    }

    public function create(User $user)
    {
        return $user->can('contact-create');
    }

    public function show(User $user, Contact $contact)
    {
        return $user->id == $contact->user_id || ($user->can('contact-view') && $contact->tenant_id == $user->tenant_id);
    }

    public function update(User $user, Contact $contact)
    {
        return $user->id == $contact->user_id || ($user->can('contact-update') && $contact->tenant_id == $user->tenant_id);
    }

    public function delete(User $user, Contact $contact)
    {
        return ($user->can('contact-delete') && $contact->tenant_id == $user->tenant_id) 
            && (!$user->contact || $user->contact->id != $contact->id); // User cannot delete own contact
    }
    
    public function import(User $user)
    {
        return $user->can('contact-create') && $user->can('contact-update');
    }

}
