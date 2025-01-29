@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <h1 class="text-muted">@lang('This event do not allow to check in contacts')</h1>
    </div>
</div>

@endsection
