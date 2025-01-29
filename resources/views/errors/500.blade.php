@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">@lang('500')</h1>
                <h4 class="pt-3">@lang('Houston, we have a problem!')</h4>
                <p class="text-muted">@lang('The page you are looking for is temporarily unavailable.')</p>
                <p class="text-muted">{{ $exception->getMessage() }}</p>
            </div>
        </div>
    </div>
</div>

@endsection