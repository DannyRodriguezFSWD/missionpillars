<?php

namespace App\Traits\Groups;

use App\Models\Contact;
use App\Constants;

/**
 *
 * @author josemiguel
 */
trait MembersManagment {
    public function sort($sort) {
        switch ($sort) {
            case 'lastname':
                $field = 'last_name';
                break;
            case 'email':
                $field = 'email_1';
                break;
            default :
                $field = 'first_name';
                break;
        }
        return $field;
    }
    
    public function getData($group, $request) {
        if ($request->has('sort') && $request->has('order')) {
            $sort = array_get($request, 'sort');
            $order = array_get($request, 'order');
            $field = $this->sort($sort);
            $contacts = $group->contacts()->where('is_private', 0);
            if (!auth()->user()->can('contacts-view-under-18')) {
                $contacts->whereRaw('(dob is null or TIMESTAMPDIFF(YEAR, dob, now()) >= 18)');
            }
            $contacts->orderBy($field, $order);
            $nextOrder = ($order === 'asc') ? 'desc' : 'asc';
        } else {
            $contacts = $group->contacts()->where('is_private', 0);
            if (!auth()->user()->can('contacts-view-under-18')) {
                $contacts->whereRaw('(dob is null or TIMESTAMPDIFF(YEAR, dob, now()) >= 18)');
            }
            $contacts->orderByDirectorySort();
            
            $sort = null;
            $order = 'asc';
            $nextOrder = 'asc';
        }
        
        $data = [
            'group' => $group,
            'contacts' => $contacts->paginate(20),
            'sort' => $sort, 
            'order' => $order, 
            'nextOrder' => $nextOrder, 
            'total' => $contacts->count()
        ];
        return $data;
    }

    public function syncMembers($group, $members, $detach) {
        $group->contacts()->detach($detach);
        $group->contacts()->sync($members, false);
    }

    public function detachMembers($group, $members) {
        $group->contacts()->detach($members);
    }

    public function syncLeaders($group, $leaders, $detach) {
        $group->leaders()->detach($detach);
        $group->leaders()->sync($leaders, false);
    }

    public function detachleaders($group, $leaders) {
        $group->leaders()->detach($leaders);
    }

    public function searchContacts($group, $request) {
        $keyword = array_get($request, 'keyword');
        $keywordEx = explode(' ', trim($keyword));
        
        if (array_has($request, 'action') && array_get($request, 'action') === 'add') {
            $contacts = Contact::whereRaw('true');
            foreach ($keywordEx as $search) {
                $contacts = $contacts->where(function ($query) use ($search) {
                    return $query->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email_1', 'like', "%$search%");
                }); 
            }
            return $contacts->paginate();
        }

        if (array_has($request, 'action') && array_get($request, 'action') === 'remove') {
            $contacts = $group->contacts();
            foreach ($keywordEx as $search) {
                $contacts = $contacts->where(function ($query) use ($search) {
                    return $query->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email_1', 'like', "%$search%");
                }); 
            }
            return $contacts->paginate();
        }

        if (array_has($request, 'action') && array_get($request, 'action') === 'addleader') {
            $leaders = array_pluck($group->leaders, 'id');
            $contacts = $group->contacts()->whereNotIn('id', $leaders);
            foreach ($keywordEx as $search) {
                $contacts = $contacts->where(function ($query) use ($search) {
                    return $query->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email_1', 'like', "%$search%");
                }); 
            }
            return $contacts->paginate();
        }
        
        if (array_has($request, 'action') && array_get($request, 'action') === 'removeleader') {
            $leaders = array_pluck($group->leaders, 'id');
            $contacts = $group->contacts()->whereIn('id', $leaders);
            foreach ($keywordEx as $search) {
                $contacts = $contacts->where(function ($query) use ($search) {
                    return $query->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email_1', 'like', "%$search%");
                }); 
            }
            return $contacts->paginate();
        }
    }
}
