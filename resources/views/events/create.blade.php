@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.create') !!}
@endsection
@section('content')
        {{ Form::open(['route' => 'events.store', 'id' => 'form', 'files' => true]) }}
        <div class="row">
            <div class="col-sm-8 offset-md-2 text-right pb-2 position-sticky" style="top: 60px; z-index: 2; pointer-events: none">
                <button type="submit" class="btn btn-primary" style="pointer-events: auto">
                    <i class="icons icon-note"></i>
                    @lang('Save')
                </button>
            </div>
            <div class="col-md-8 offset-md-2">
                @include('events.includes.fragments.event')
            </div>
        </div>
        {{ Form::close() }}
@include('events.includes.fragments.ticket-option-modal')
@include('events.includes.fragments.scripts')

@endsection
