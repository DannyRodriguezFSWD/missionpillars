@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">@lang('Oh! oh!')</h1>
                <h4 class="pt-3">@lang('Trying to cheat?')</h4>
                <p class="text-muted">@lang('This action will be logged for future references.')</p>
            </div>
        </div>
    </div>
</div>

@endsection