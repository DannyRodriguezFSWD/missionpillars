@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('roles.index') !!}
@endsection

@section('content')

@can('create', \App\Models\Role::class)
<div class="row text-right mb-3">
    <div class="col-12">
        <a href="{{ route('roles.create') }}" class="btn btn-success">
            <i class="fa fa-plus"></i> @lang('Add New Role')
        </a>
    </div>
</div>
@endcan    

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-0">{{ $total }}</h4>
                <p>@lang('Roles')</p>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="4">@lang('Role Name')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td class="align-middle">{{ $role->display_name }}</td>
                                <td class="text-right">
                                    <a href="{{route('users.index')}}?roleid={{$role->id}}" class="btn btn-link">
                                        <i class="fa fa-users"></i> View users
                                    </a>
                                </td>
                                <td class="text-right">
                                    @can('update', $role)
                                        @if (!is_null($role->tenant_id))
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-link">
                                                <span class="fa fa-edit"></span> Edit Role
                                            </a>
                                    @endcan
                                    @else
                                        <a href="{{ route('roles.show', $role) }}" class="btn btn-link">
                                            <span class="fa fa-eye"></span> View Permissions
                                        </a>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @can('delete',$role)
                                        @if( !is_null($role->tenant_id) )
                                            {{ Form::open( ['route' => ['roles.destroy', $role->id], 'method' => 'DELETE', 'id' => 'form-'.$role->id] )  }}
                                            {{ Form::hidden('uid', Crypt::encrypt($role->id)) }}
                                            {{ Form::close() }}
                                            <button type="button" class="btn btn-link delete" data-form="#form-{{ $role->id }}" data-name="{{ $role->name }}" data-toggle="modal" data-target="#delete-modal">
                                                <i class="fa fa-trash text-danger"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('lists.includes.delete-modal')

@endsection
