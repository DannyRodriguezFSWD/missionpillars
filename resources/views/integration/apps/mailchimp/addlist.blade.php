@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @lang('Add Mailchimp List')
    </div>
    <div class="card-body">
        {{ Form::open(['route' => ['mailchimp.storelist', $id]]) }}
        <div class="row">
            <div class="col-md-6">

            </div>
            <div class="col-md-6 text-right">
                <button id="btn-submit" type="submit" class="btn btn-primary"><i class="icons icon-note"></i> @lang('Save')</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('name', 'List Name') }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'List Name', 'required' => true]) }}
                </div>
            </div>
            <div class="col-md-6">
                {{ Form::label('company', 'Company') }}
                {{ Form::text('company', auth()->user()->tenant->organization, ['class' => 'form-control', 'placeholder' => 'Company', 'required' => true]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('address1', 'Address') }}
                    {{ Form::text('address1', null, ['class' => 'form-control', 'placeholder' => 'Address', 'required' => true]) }}
                </div>
            </div>
            <div class="col-md-6">
                {{ Form::label('city', 'City') }}
                {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => 'City', 'required' => true]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('state', 'State') }}
                    {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => 'State', 'required' => true]) }}
                </div>
            </div>
            <div class="col-md-4">
                {{ Form::label('zip', 'Zip Code') }}
                {{ Form::text('zip', null, ['class' => 'form-control', 'placeholder' => 'Zip Code', 'required' => true]) }}
            </div>
            <div class="col-md-4">
                {{ Form::label('country', 'Country') }}
                {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => 'Country', 'required' => true]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('permission_reminder', 'Permission Reminder') }}
                    {{ Form::textarea('permission_reminder', null, ['class' => 'form-control', 'placeholder' => 'Permission Reminder', 'required' => true]) }}
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('from_name', 'From Name') }}
                    {{ Form::text('from_name', auth()->user()->tenant->organization, ['class' => 'form-control', 'placeholder' => 'From Name', 'required' => true]) }}
                </div>
            </div>
            <div class="col-md-6">
                {{ Form::label('from_email', 'From Email') }}
                {{ Form::text('from_email', auth()->user()->tenant->email, ['class' => 'form-control', 'placeholder' => 'From Email', 'required' => true]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('subject', 'Subject') }}
                {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Subject', 'required' => true]) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('language', 'Language') }}
                {{ Form::text('language', 'en', ['class' => 'form-control', 'placeholder' => 'Language', 'required' => true]) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>

@endsection
