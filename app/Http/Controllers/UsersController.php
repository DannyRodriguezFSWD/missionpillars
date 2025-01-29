<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Role;

use App\Http\Requests\Users\StoreUser;
use App\Http\Requests\Users\UpdateUser;
use App\Http\Requests\Users\ListUsers;
use App\Constants;

class UsersController extends Controller {

    const PERMISSION = 'crm-users';

    private function sort($sort) {
        switch ($sort) {
            case 'lastname':
                $field = 'last_name';
                break;
            case 'email':
                $field = 'email';
                break;
            default :
                $field = 'name';
                break;
        }
        return $field;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ListUsers $request) {
        $this->checkIfFeatureEnabled();

        $role_id = request('roleid', null);
        $sort = 'firstname';
        $order = 'asc';
        if ($request->has('sort')) $sort = array_get($request, 'sort');
        $field = $this->sort($sort);
        if ($request->has('order')) $order = array_get($request, 'order');

        $users = User::orderBy($field, $order);
        $nextOrder = ($order === 'asc') ? 'desc' : 'asc';

        $total = $users->with('roles')
        ->whereHas('roles', function ($query) use ($role_id) {
                    $query->where('name', '!=', '');
                    if ($role_id) $query->where('id',$role_id);
                })->get();
        $data = [
            'users' => $users->paginate(),
            'sort' => $sort,
            'order' => $order,
            'nextOrder' => $nextOrder,
            'searchedRole' => Role::find($role_id),
            'total' => $total->count()
        ];

        return view('people.users.index')->with($data);
    }

    public function checkIfFeatureEnabled()
    {
        if(!auth()->user()->tenant->can(self::PERMISSION)){
            redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION])->send();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $this->authorize('create',User::class);
        $this->checkIfFeatureEnabled();
        $r = Role::where('tenant_id', array_get(auth()->user(), 'tenant.id'))
        ->orWhereNull('tenant_id')->get();
        $roles = collect($r)->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'display_name');
            return $carry;
        }, ['null' => 'Select Role']);
        $data = ['roles' => $roles];
        return view('people.users.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUser $request) {
        $user = new User();
        array_set($user, 'name', array_get($request, 'name'));
        array_set($user, 'last_name', array_get($request, 'last_name'));
        array_set($user, 'email', array_get($request, 'email'));
        array_set($user, 'password', bcrypt(array_get($request, 'password')));
        
        if (!auth()->user()->tenant->users()->save($user)) abort(500);
        
        if (auth()->user()->can('role-change')) $user->roles()->attach(array_get($request, 'role')); // id only
        else {
            // if unauthorized, used the 'contact' role as a default
            $role = Role::where('name', 'organization-contact')->first();
            $user->attachRole($role);
        }
        
        if(is_null(array_get($user, 'contact'))){
            $user->createContact();
        }
        if (auth()->user()->cannot('edit', $user->contact)) {
            return redirect()->route('users.index')->with('message', __('User successfully added'));
        }
        
        return redirect()->route('contacts.edit', ['id' => $user->contact->id])->with('message', __('User successfully added'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) 
    {
        return redirect(route('users.edit', $id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request) {
        $this->checkIfFeatureEnabled();
        $user = User::findOrFail($id);
        $this->authorize('update',$user);
        $r = Role::where([
            ['tenant_id', '=', array_get(auth()->user(), 'tenant.id')]
        ])->orWhereNull('tenant_id')->get();
        $roles = collect($r)->reduce(function($carry, $item){
            $carry[array_get($item, 'id')] = array_get($item, 'display_name');
            return $carry;
        }, ['null' => 'Select Role']);

        $data = [
            'user' => $user,
            'uid' => Crypt::encrypt($id),
            'roles' => $roles
        ];

        return view('people.users.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUser $request) {
        $user = $request->user_;
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        if ($request->has('password')) {

            array_set($user, 'password', bcrypt(array_get($request, 'password')));

            if ($request->has('force')) {
                array_set($user, 'one_time_hash', bcrypt(array_get($request, 'password')));
            }
        }
        if ($user->update()) {
            if ($request->has('role') && $request->role != "null") $user->roles()->sync(array_get($request, 'role'));
            if (is_null($user->contact)) $user->createContact();
            return redirect()->route('users.edit', ['id' => array_get($user, 'id')])->with('message', __('User updated successfully'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {
        $user = User::findOrFail($id);
        $this->authorize('delete',$user);
        
        $contact = $user->contact;
        
        if ($contact) {
            $contact->user_id = null;
            $contact->save();
        }
        
        User::destroy($id);
        return redirect()->route('users.index')->with('message', __('User was successfully deleted'));
    }

}
