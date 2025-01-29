<?php

namespace App\Observers;

use App\Models\User;
use App\Constants;
use App\Models\Contact;
use Carbon\Carbon;

/**
 * Description
 *
 * @author josemiguel
 */
class UserObserver 
{
    public function created(User $user) 
    {
        $contact =  Contact::withoutGlobalScopes()->whereNull('deleted_at')->where('user_id', array_get($user, 'id'))->first();
        
        if (is_null($contact)) {
            $contact = Contact::withoutGlobalScopes()->whereNull('deleted_at')->where('tenant_id', array_get($user, 'tenant.id'))->whereNull('user_id')->where(function ($query) use ($user) {
                $query->where('email_1', array_get($user, 'email'))->orWhere('email_2', array_get($user, 'email'));
            })->first();
        }
        
        if (is_null($contact)) {
            $user->createContact();
        } else {
            array_set($contact, 'user_id', array_get($user, 'id'));
            
            if (!array_get($contact, 'first_name')) {
                array_set($contact, 'first_name', array_get($user, 'name'));
            }
            
            if (!array_get($contact, 'last_name')) {
                array_set($contact, 'last_name', array_get($user, 'last_name'));
            }
            
            if (!array_get($contact, 'last_name')) {
                array_set($contact, 'last_name', array_get($user, 'last_name'));
            }
            
            $request = request();
            
            if (!array_get($contact, 'cell_phone')) {
                array_set($contact, 'cell_phone', array_get($request, 'phone'));
            }
            
            $contact->update();
            
            array_set($user, 'name', array_get($contact, 'first_name'));
            array_set($user, 'last_name', array_get($contact, 'last_name'));
            $user->update();
        }
    }
}
