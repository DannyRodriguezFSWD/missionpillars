@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <a href="{{ route('api.index') }}">
            <span class="fa fa-chevron-left"></span>
            @lang('Back To API Keys')
        </a>
    </div>
    <div class="card-body">
        <h4 class="mb-0">@lang('Create New API Key')</h4>
        <p>&nbsp;</p>
        {{ Form::open(['route' => 'api.store']) }}
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <span class="text-danger">*</span> 
                    {{ Form::label('name', __('API Key Name')) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'API Key Name', 'autocomplete' => 'off']) }}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-right">
                <button class="btn btn-primary">
                    <span class="fa fa-edit"></span>
                    @lang('Save')
                </button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
    
    <div class="card-footer">&nbsp;</div>
</div>

@endsection