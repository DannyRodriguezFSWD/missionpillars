@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.index') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <h3>@lang('Events')</h3>
        <div class="btn-group flex-wrap" id="new-event" role="group" aria-label="...">
            @can('create',\App\Models\CalendarEvent::class)
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    <i class="fa fa-calendar-plus-o"></i>
                    @lang('Add New Event')
                </a>
            @endcan
            <a href="{{ route('calendars.index') }}" class="btn btn-primary">
                <i class="fa fa-calendar-o"></i>
                @lang('Manage Calendars')
            </a>
            <button class="btn btn-primary" data-toggle="modal" data-target="#share-modal">
                <i class="fa fa-share-alt"></i>
                @lang('Share Calendars')
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#export-tickets-filter-modal"">
                <span class="fa fa-file-excel-o"></span> @lang('Export Tickets')
            </button>
            <button id="show-calendar" data-calendar="0" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @lang('Show all calendars')
            </button>
            <div class="dropdown-menu" x-placement="bottom-start">
                <button class="dropdown-item calendar-option" data-id="0" data-color="#ffffff" data-border="#adadad">
                    @lang('Show all calendars')
                </button>
                @foreach( $calendars as $calendar )
                <button class="dropdown-item calendar-option" data-id="{{ array_get($calendar, 'id') }}" data-color="{{ array_get($calendar, 'color') }}">
                    <i class="fa fa-square" style="color: {{ array_get($calendar, 'color') }}"></i>
                    {{ array_get($calendar, 'name') }}
                </button>
                @endforeach
            </div>
        </div>
        <p>&nbsp;</p>
        <div id="calendar"></div>
    </div>
</div>


@include('events.includes.actions-event-modal')
@include('events.includes.index-scripts')
@include('events.includes.share-modal')
@include('events.includes.export-tickets-filter-modal')

@endsection