@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mx-4">
                <div class="card-body p-4">
                    <h1>@lang('Password recovery')</h1>
                    <p class="text-muted">@lang('Enter your E-Mail address')</p>
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <small><strong>{{ $errors->first('email') }}</strong></small>
                        </span>
                        @endif
                        <div class="input-group mb-3 {{$errors->has('email') ? 'has-danger has-feedback':''}}">
                            <span class="input-group-prepend"><i class="input-group-text fa fa-envelope"></i></span>
                            <input type="text" name="email" class="form-control {{$errors->has('email') ? 'form-control-danger':''}}" placeholder="Email">
                        </div>
                        <button type="submit" class="btn btn-block btn-success">
                            <span class="icon icon-paper-plane"></span> @lang('Send Password Reset Link')
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
