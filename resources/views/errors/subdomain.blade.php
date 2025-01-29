@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">@lang('Oh! oh!')</h1>
                <h4 class="pt-3">@lang("The subdomain you are trying to register has already registered.")</h4>
                <p class="text-muted">@lang('Contact with subdomain admin to create an account.')</p>
            </div>
        </div>
    </div>
</div>

@endsection