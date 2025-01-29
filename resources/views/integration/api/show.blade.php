@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <a href="{{ route('api.index') }}">
            <span class="fa fa-chevron-left"></span>
            @lang('Back To API Keys')
        </a>
    </div>
    <div class="card-body">
        <h4 class="mb-0">{{ $key->name }}</h4>
        <p>@lang('API Key')</p>
        <textarea class="form-control" readonly="true" rows="20">{{ $key->token }}</textarea>
    </div>
    
    <div class="card-footer">&nbsp;</div>
</div>

@endsection