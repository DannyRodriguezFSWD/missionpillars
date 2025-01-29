@extends('layouts.children-checkin')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <a href="{{ route('child-checkin.index') }}" class="btn btn-success btn-lg btn-block">
            <span class="fa fa-rotate-left"></span> @lang('Start Over')
        </a>
    </div>
</div>

<div class="container children">
    
    <div class="row justify-content-center">
        <div class="col-sm-12">
            @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('message') }}
            </div>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-12 titles">
            <h1>{{ $tenant->organization }}</h1>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-12 col-sm-offset-10">
            <div class="card p-4">
                {{ Form::open(['route' => 'child-checkin.store']) }}
                {{ Form::hidden('action', $action) }}
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('Add Myself')</h1>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('first_name') ? 'has-danger':''}}">
                            {{ Form::label('first_name', __('First Name')) }}
                            @if ($errors->has('first_name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('first_name') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('first_name', null , ['class' => 'form-control', 'placeholder' => __('First Name'), 'required'=>true, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('last_name') ? 'has-danger':''}}">
                            {{ Form::label('last_name', __('Last Name')) }}
                            @if ($errors->has('last_name'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('last_name') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('last_name', null , ['class' => 'form-control', 'placeholder' => __('Last Name'), 'required'=>true, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('dob') ? 'has-danger':''}}">
                            {{ Form::label('dob', __('Birthday')) }}
                            @if ($errors->has('dob'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('dob') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('dob', null, ['class' => 'form-control calendar readonly', 'placeholder' => __('Birthday'), 'required' => true, 'readonly' => true]) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('gender') ? 'has-danger':''}}">
                            {{ Form::label('gender', __('Gender')) }}
                            {{ Form::select('gender', ['Male' => __('Male'), 'Female' => __('Female')], null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {{ Form::label('cell_phone', __('Cell Phone')) }}
                            @if ($errors->has('cell_phone'))
                            <span class="help-block text-danger">
                                <small><strong>{{ $errors->first('cell_phone') }}</strong></small>
                            </span>
                            @endif
                            {{ Form::text('cell_phone', null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <!--
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('relationship') ? 'has-danger':''}}">
                            {{ Form::label('relationship', __('Relationship')) }}
                            {{ Form::select('relationship', $relationships, null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    -->
                </div>

                <div class="row">
                    <div class="col-sm-12 text-right">
                        <button id="btn-submit-contact" type="submit" class="btn btn-primary"><i class="fa fa-edit"></i> @lang('Save')</button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>

    </div>

</div>

@endsection
