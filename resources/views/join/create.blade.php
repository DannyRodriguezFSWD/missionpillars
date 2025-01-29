@extends('layouts.auth-forms')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card mx-8">
                <div class="card-body p-4">
                    {{Form::open(['route' => ['join.store']])}}
                    {{ Form::hidden('registry', $registry) }}
                    <h2>{{$tenant->organization}}</h2>
                    <p>&nbsp;</p>
                    @if ($errors->has('first_name'))
                        <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('first_name') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('first_name') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="icon-user input-group-text"></i></span>
                        {{ Form::text('first_name', null, ['class'=>'form-control', 'placeholder'=>__('First Name'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>

                    @if ($errors->has('last_name'))
                        <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('last_name') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('last_name') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="icon-user input-group-text"></i></span>
                        {{ Form::text('last_name', null, ['class'=>'form-control', 'placeholder'=>__('Last Name'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>

                    @if ($errors->has('email_1'))
                        <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('email_1') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('email_1') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><span class="input-group-text">@</span></span>
                        {{ Form::email('email_1', null, ['class'=>'form-control', 'placeholder'=>__('Email'), 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                    @if(is_null($registry))
                        @if ($errors->has('cell_phone'))
                            <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('cell_phone') }}
                            </strong>
                        </small>
                    </span>
                        @endif
                        <div class="input-group mb-3 {{$errors->has('cell_phone') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="icon-phone input-group-text"></i>
                        </span>
                            {{ Form::text('cell_phone', null, ['class' => 'form-control', 'placeholder' => __('Phone number'), 'autocomplete' => 'off']) }}
                        </div>

                        @if ($errors->has('mailing_address_1'))
                            <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('mailing_address_1') }}
                            </strong>
                        </small>
                    </span>
                        @endif
                        <div class="input-group mb-3 {{$errors->has('mailing_address_1') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="icon-home input-group-text"></i>
                        </span>
                            {{ Form::text('mailing_address_1', null, ['class' => 'form-control', 'placeholder' => __('Mailing Address'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="input-group mb-3 {{$errors->has('city') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="icon-home input-group-text"></i>
                        </span>
                            {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('City'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="input-group mb-3 {{$errors->has('region') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="icon-home input-group-text"></i>
                        </span>
                            {{ Form::text('region', null, ['class' => 'form-control', 'placeholder' => __('Region'), 'autocomplete' => 'off']) }}
                        </div>
                        <div class="input-group mb-3 {{$errors->has('region') ? 'has-danger has-feedback':''}}">
                            {{ Form::select('country_id', $countries, null, ['class' => 'form-control']) }}
                        </div>
                    @endif
                    <div class="text-right">
                        @include('shared.sessions.start-over-button',['class' => 'btn btn-link btn-lg'])
                        <button type="submit" class="btn btn-success btn-lg">@lang('Join Now')</button>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
    </div>

@endsection
