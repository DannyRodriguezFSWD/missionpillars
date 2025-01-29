@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('roles.edit',$role) !!}
@endsection
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            {{ Form::model($role, ['route' => ['roles.update', $role->id], 'method' => 'PUT']) }}
            {{ Form::hidden('uid', Crypt::encrypt($role->id)) }}
            <div class="card-header">
                <span class="h4">Edit {{ $role->display_name }}</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <span class="text-danger">*</span> 
                    {{ Form::label('name', __('Role')) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    <span class="text-danger">*</span> 
                    {{ Form::label('descripcion', __('Description')) }}
                    {{ Form::textarea('description', null, ['class' => 'form-control', 'required' => true]) }}
                </div>
            </div>
            <button id="btn-submit-contact" type="submit" class="btn btn-primary" style="position: fixed; right: 36px; top: 130px; z-index: 99;">
                <i class="icons icon-note"></i> @lang('Save')
            </button>

            @include('roles.includes.permissions')

            {{ Form::close() }}
        </div>
    </div>
</div>

@include('lists.includes.delete-modal')

@endsection
