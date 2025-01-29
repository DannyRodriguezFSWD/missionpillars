@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">@lang('401')</h1>
                <h4 class="pt-3">@lang("Oops!")</h4>
                <p class="text-muted">@lang("You're not authorized to perform this action.")</p>
            </div>
        </div>
    </div>
</div>

@endsection
