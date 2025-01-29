@extends('layouts.auth-forms')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-header">@lang('Reset Password')</div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif
                    
                    <h3>{{ array_get($tenant, 'organization') }}</h3>

                    <form role="form" method="POST" action="{{ route('password.request') }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group{{ $errors->has('email') ? ' has-error has-danger' : '' }}">
                            <label for="email" class="control-label">@lang('E-Mail Address')</label>


                            <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus autocomplete="off">

                            @if ($errors->has('email'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif

                        </div>

                        <label for="password" class="control-label">@lang('Password')</label>
                        <div class="form-group{{ $errors->has('password') ? ' has-error has-danger' : '' }}">
                            


                            <input id="password" type="password" class="form-control" name="password" required autocomplete="off">

                            @if ($errors->has('password'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                            @endif

                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error has-danger' : '' }}">
                            <label for="password-confirm" class="control-label">@lang('Confirm Password')</label>

                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="off">

                            @if ($errors->has('password_confirmation'))
                            <span class="help-block text-danger">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                            @endif

                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Reset Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>
@endsection
