@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mx-4">
                <div class="card-header">
                    <a href="{{ route('login') }}">
                        <span class="icon icon-arrow-left"></span> @lang('Back to log in')
                    </a>
                </div>
                {{Form::open(['route' => 'customregister'])}}
                <div class="card-body p-4">
                    {{ Form::hidden('isSubdomain',  $showSubdomainField ? 0 : 1) }}

                    @if(!$showSubdomainField)
                    <h2>@lang('Welcome back to') {{ $tenant->organization }}</h2>
                    {{ Form::hidden('subdomain', Crypt::encrypt($tenant->id)) }}
                    @else
                    <h2>@lang('Register')</h2>
                    @endif
                    <p class="text-muted">@lang('Get started with a free Mission Pillars account. No credit card required.')</p>

                    @if ($errors->has('name'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('name') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('name') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="input-group-text fa fa-user"></i></span>
                        {{ Form::text('name', null, ['class'=>'form-control '.($errors->has('name') ? 'is-invalid' : ''), 'placeholder'=>__('Name')]) }}
                    </div>

                    @if ($errors->has('lastname'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('lastname') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('lastname') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="input-group-text fa fa-user"></i></span>
                        {{ Form::text('lastname', null, ['class'=>'form-control '.($errors->has('lastname') ? 'is-invalid' : ''), 'placeholder'=>__('Last Name')]) }}
                    </div>

                    @if ($errors->has('email'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('email') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('email') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="input-group-text fa fa-envelope"></i></span>
                        {{ Form::email('email', null, ['class'=>'form-control '.($errors->has('email') ? 'is-invalid' : ''), 'placeholder'=>__('Email')]) }}
                    </div>

                    @if($showSubdomainField)
                    @if ($errors->has('subdomain'))
                    <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('subdomain') }}
                            </strong>
                        </small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('subdomain') ? 'has-danger has-feedback':''}}">
                        {{ Form::text('subdomain', null, ['class' => 'form-control '.($errors->has('subdomain') ? 'is-invalid' : ''), 'placeholder' => __('Subdomain')]) }}
                        <span class="input-group-append">
                            <span class="input-group-text">{{ $domain }}</span>
                        </span>
                    </div>

                    @if ($errors->has('organization'))
                    <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('organization') }}
                            </strong>
                        </small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('organization') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="input-group-text fa fa-group"></i>
                        </span>
                        {{ Form::text('organization', null, ['class' => 'form-control '.($errors->has('organization') ? 'is-invalid' : ''), 'placeholder' => __('Organization')]) }}
                    </div>

                    @if ($errors->has('website'))
                    <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('website') }}
                            </strong>
                        </small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('website') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="input-group-text fa fa-globe"></i>
                        </span>
                        {{ Form::text('website', null, ['class' => 'form-control '.($errors->has('website') ? 'is-invalid' : ''), 'placeholder' => __('Website')]) }}
                    </div>
                    @endif

                    @if ($errors->has('phone'))
                    <span class="help-block text-danger">
                        <small>
                            <strong>
                                {{ $errors->first('phone') }}
                            </strong>
                        </small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('phone') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend">
                            <i class="input-group-text fa fa-phone"></i>
                        </span>
                        {{ Form::text('phone', null, ['class' => 'form-control '.($errors->has('phone') ? 'is-invalid' : ''), 'placeholder' => __('Phone number')]) }}
                    </div>

                    @if ($errors->has('password'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('password') }}</strong></small>
                    </span>
                    @endif
                    <div class="input-group mb-3 {{$errors->has('password') ? 'has-danger has-feedback':''}}">
                        <span class="input-group-prepend"><i class="input-group-text fa fa-lock"></i></span>
                        {{ Form::password('password', ['class'=>'form-control '.($errors->has('password') ? 'is-invalid' : ''), 'placeholder'=>__('Password')]) }}
                    </div>

                    <div class="input-group mb-4">
                        <span class="input-group-prepend"><i class="input-group-text fa fa-lock"></i></span>
                        {{ Form::password('password_confirmation', ['class'=>'form-control '.($errors->has('password_confirmation') ? 'is-invalid' : ''), 'placeholder'=>__('Repeat password')]) }}
                    </div>

                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                        {{ Form::hidden('recaptcha', null, ['class' => 'form-control d-none', 'data-recaptcha' => true, 'required' => true, 'data-error' => 'Please complete the Captcha']) }}
                        @if ($errors->has('recaptcha'))
                        <span class="help-block text-danger">
                            <small>
                                <strong>
                                    {{ $errors->first('recaptcha') }}
                                </strong>
                            </small>
                        </span>
                        @endif
                    </div>
                    <script src='https://www.google.com/recaptcha/api.js'></script>
                    
                    <div class="card-body">
                        <button type="submit" class="btn btn-block btn-primary btn-lg">
                            @lang('Create Account')
                        </button>
                    </div>
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
</div>

@if (!$showSubdomainField && $errors->has('email') && $errors->first('email') === 'The email has already been taken.')
    @push('scripts')
    <script>
        Swal.fire({
            title: 'This email already exists in our system, do you want to sign in instead?',
            type: 'error',
            showCancelButton: true,
            confirmButtonText: '<span class="icon icon-key"></span> Sign In'
        }).then(function (result) {
            if (result.value) {
                window.location.href = '{{ route('login') }}';
            }
        });
    </script>
    @endpush
@endif

@endsection
