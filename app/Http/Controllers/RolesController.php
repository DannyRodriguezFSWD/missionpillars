<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Constants;
use Illuminate\Support\Facades\Gate;

class RolesController extends Controller
{
    const PERMISSION = 'crm-roles';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->checkIfFeatureEnabled();
        $roles = Role::where('tenant_id', array_get(auth()->user(), 'tenant.id'))
            ->orWhereNull('tenant_id')
            ->orderBy('tenant_id', 'asc')
            ->orderBy('id', 'asc');
        $total = $roles->count();
        $data = [
            'roles' => $roles->paginate(),
            'total' => $total
        ];

        return view('roles.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->checkIfFeatureEnabled();
        $this->authorize('create', Role::class);

        $permissions = Permission::orderBy('group_name')->orderBy('display_name')->get();
        $permissionGroups = Permission::groupPermissions($permissions);
        
        $data = ['permissionGroups' => $permissionGroups, 'attached' => []];
        
        return view('roles.create')->with($data);
    }

    private function checkIfFeatureEnabled()
    {
        if (Gate::denies('feature', Role::class)) redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION])->send();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Role::class);
        $role = new Role();
        array_set($role, 'name', array_get($request, 'name'));
        array_set($role, 'description', array_get($request, 'description'));
        array_set($role, 'display_name', array_get($request, 'name'));
        array_set($role, 'slug', str_slug(array_get($request, 'name')));

        if (auth()->user()->tenant->roles()->save($role)) {
            $role->perms()->sync(array_get($request, 'permissions'));
            return redirect()->route('roles.edit', ['id' => array_get($role, 'id')])->with('message', __('Role successfully added'));
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->checkIfFeatureEnabled();
        
        $role = Role::where('id', $id)->where(function ($query) {
            $query->where('tenant_id', array_get(auth()->user(), 'tenant.id'))->orWhereNull('tenant_id');
        })->first();
        
        if (is_null($role)) {
            abort(404);
        }
        
        $permissions = Permission::orderBy('group_name')->orderBy('display_name')->get();
        $permissionGroups = Permission::groupPermissions($permissions);
        
        $attached = array_pluck($role->perms, 'id');
        
        return view('roles.show')->with(compact('role', 'permissionGroups', 'attached'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->checkIfFeatureEnabled();
        $role = Role::where([
            ['tenant_id', '=', array_get(auth()->user(), 'tenant.id')],
            ['id', '=', $id],
        ])->first();
        $this->authorize('update', $role);
        if (is_null($role)) {
            abort(404);
        }

        $permissions = Permission::orderBy('group_name')->orderBy('display_name')->get();
        $permissionGroups = Permission::groupPermissions($permissions);
        
        $data = [
            'role' => $role,
            'permissionGroups' => $permissionGroups,
            'attached' => array_pluck($role->perms, 'id')
        ];
        return view('roles.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);
        if (is_null(array_get($role, 'tenant_id'))) {
            abort(404);
        }
        array_set($role, 'name', array_get($request, 'name'));
        array_set($role, 'description', array_get($request, 'description'));
        array_set($role, 'display_name', array_get($request, 'name'));
        array_set($role, 'slug', str_slug(array_get($request, 'name')));

        if ($role->update()) {
            $role->perms()->sync(array_get($request, 'permissions'));
            return redirect()->route('roles.edit', ['id' => array_get($role, 'id')])->with('message', __('Role successfully updated'));
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        $this->authorize('delete', $role);
        Role::whereId($id)->delete();
        return redirect()->route('roles.index')->with('message', __('Role successfully deleted'));
    }
}
