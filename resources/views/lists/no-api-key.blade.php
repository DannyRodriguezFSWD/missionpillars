@extends('layouts.app')

@section('content')

<div class="card card-accent-danger">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <p>@lang("In order to use lists, a <a target='_blank' href='http://mailchimp.com'>Mailchimp</a> API Key its required")</p>
    </div>
    <div class="card-footer text-right">
        <a class="btn btn-primary" href="{{ route('integrations.index') }}">
            @lang('Go to thirth party apps integration')
        </a>
    </div>
</div>


@endsection
