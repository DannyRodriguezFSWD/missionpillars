@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('events.index') }}">@lang('Events')</a>
            </li>
            <li class="breadcrumb-item active">{{ $event->name }}</li>
        </ol>
        <div class="row">
            @include('events.includes.event-settings-menu')
            <div class="col-sm-9 vertical-menu-bar">
                
            </div>
        </div>
    </div>
</div>

@endsection
