@extends('layouts.auth-forms')

@section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mx-4 rounded-xl">
                {{ Form::open(['route' => 'oneclick.store', 'id' => 'register']) }}
                <div class="card-body p-4">
                    @if(!isset($tenant))
                    <div class="text-center">
                        <img src="{{ asset('img/free 14 day trial.jpg') }}" alt="Free 14 day trial" />
                    </div>
                    @endif
                    
                    {{ Form::hidden('one_time_token') }}

                    @if(isset($tenant))
                    <h1>@lang('One Click Register')</h1>
                    @else
                    <h1>@lang('Claim your free '.App\Constants::MODULE_FREE_DAYS.' day trial')</h1>
                    @endif
                    <p class="text-muted">@lang('Get started with a free Mission Pillars account. No credit card required.')</p>

                    @if ($errors->has('subdomain'))
                    <span class="help-block text-danger">
                        <small><strong>@lang('The subdomain has already been taken.')</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('subdomain') ? 'has-danger':''}}">
                        @if($readonly)
                            {{ Form::text('site', array_get($tenant, 'subdomain').'.'.$domain , ['class' => 'form-control input-validate', 'placeholder' => __('subdomain'), 'value'=>old('subdomain'), 'readonly' => true]) }}
                            {{ Form::hidden('readonly', true) }}
                            {{ Form::hidden('subdomain', array_get($tenant, 'subdomain')) }}
                        @else
                            {{ Form::label('subdomain', __('Subdomain')) }}
                            <div class="input-group mb-3 ">
                                {{ Form::text('subdomain', str_slug(array_get($data, 'data.organization_name')) , ['class' => 'form-control input-validate', 'placeholder' => __('subdomain'), 'value'=>old('subdomain')]) }}
                                <span class="input-group-addon">.{{$domain}}</span>
                            </div>
                        @endif
                    </div>
                    {{Form::hidden('ein',array_get($data,'data.ein'))}}
                    @if ($errors->has('name'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('name') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group d-none">
                        {{ Form::label('name', __('Name')) }}
                        {{ Form::text('name', array_get($data, 'data.contact_first_name'), ['class' => 'form-control', 'placeholder' => __('Name'), 'value'=>old('name')]) }}
                    </div>

                    @if ($errors->has('lastname'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('lastname') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group d-none">
                        {{ Form::label('lastname', __('Last Name')) }}
                        {{ Form::text('lastname', array_get($data, 'data.contact_last_name'), ['class' => 'form-control', 'placeholder' => __('Last Name'), 'value'=>old('lastname')]) }}
                    </div>
                    
                    <div class="form-group d-none">
                        {{ Form::label('preferred_name', __('Preferred Name')) }}
                        {{ Form::text('preferred_name', array_get($data, 'data.contact_preferred_name'), ['class' => 'form-control', 'placeholder' => __('Preferred Name'), 'value'=>old('preferred_name')]) }}
                    </div>                   
                    
                    @if ($errors->has('email'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('email') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('email') ? 'has-danger':''}}">
                        {{ Form::label('email', __('Email')) }}
                        {{ Form::email('email', array_get($data, 'data.contact_email_1'), ['class' => 'form-control input-validate d-none', 'placeholder' => __('Email'), 'value'=>old('email')]) }}
                        <br>
                        {{ array_get($data, 'data.contact_email_1') }}
                    </div>

                    @if(false)
                    @if ($errors->has('password'))
                    <span class="help-block text-danger">
                        <small><strong>{{ $errors->first('password') }}</strong></small>
                    </span>
                    @endif
                    <div class="form-group {{$errors->has('password') ? 'has-danger':''}}">
                        {{ Form::label('password', __('Password')) }}
                        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Password'), 'required'=>true]) }}
                    </div>

                    <div class="form-group">
                        {{ Form::label('password_confirmation', __('Repeat Password')) }}
                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => __('Repeat Password'), 'required'=>true]) }}
                    </div>
                    @endif

                    @if(isset($tenant))
                    {{ Form::button('Create Account', ['class'=>'btn btn-lg btn-block btn-primary mt-4', 'type'=>'button', 'id'=>'btn-submit']) }}
                    @else
                    {{ Form::button('Claim Your Free Trial', ['class'=>'btn btn-lg btn-block btn-primary mt-4', 'type'=>'button', 'id'=>'btn-submit']) }}
                    @endif
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>

</div>

<div id="overlay">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
    </div>
    @if(!$readonly)
        <p class="text-center">@lang('Please wait while we set up your account. This won\'t take a while.')</p>
        @else

        @endif
</div>

<style>
    .c-app {
        background-image: url('{{ asset('img/free trial background.jpg') }}');
        background-repeat: round;
        background-size: cover;
    }
</style>

@push('scripts')
<script type="text/javascript">

    $(function () {
        $('#btn-submit').on('click', function (e) {
            $(this).attr('disabled','disabled');
            $('#overlay').show();
            $('#register').submit();
        });

        $('.input-validate').on('keypress', function (e) {
            var inp = String.fromCharCode(e.keyCode);
            if (!/[a-zA-Z0-9-_ ]/.test(inp))
                return false;
        });
    });

</script>
@endpush
@endsection
