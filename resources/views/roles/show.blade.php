@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('roles.show', $role) !!}
@endsection

@section('content')

@can('update', $role)
<div class="row text-right mb-3">
    <div class="col-12">
        <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning">
            <span class="fa fa-edit"></span> Edit Role
        </a>
    </div>
</div>
@endcan

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <span class="h4">{{ $role->display_name }}</span>
            </div>
            
            <div class="card-body">
                <p class="lead">{{ $role->description }}</p>
                
                @include('roles.includes.permissions')
            </div>
        </div>
    </div>
</div>

@endsection
