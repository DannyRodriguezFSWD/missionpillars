@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">@lang('404')</h1>
                <h4 class="pt-3">@lang("Oops! You're lost.")</h4>
                <p class="text-muted">@lang('The page you are looking for was not found.')</p>
                <a href="/" class="btn btn-secondary">
                    Return to dashboard
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
