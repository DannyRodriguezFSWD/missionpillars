@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('roles.create') !!}
@endsection
@section('content')

<div class="card">
    {{ Form::open(['route' => ['roles.store']]) }}
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <h1>@lang('Create New Role')</h1>
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
        <h4>@lang('Permissions')</h4>
    </div>
    <button id="btn-submit-contact" type="submit" class="btn btn-primary" style="position: fixed; right: 36px; top: 130px; z-index: 99;">
        <i class="icons icon-note"></i> @lang('Save')
    </button>
    
    @include('roles.includes.permissions')
    
    {{ Form::close() }}
</div>

@include('lists.includes.delete-modal')

@endsection
