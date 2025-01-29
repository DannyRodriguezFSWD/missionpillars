@extends('layouts.auth-forms')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-6 col-10">
            <div class="card">
                <div class="card-header">
                    @if(count($calendars) > 1)
                    <div class="pull-right">
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
                    @else
                    &nbsp;
                    @endif
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
</div>

@include('calendars.includes.event-modal')

@include('events.includes.index-scripts')

@endsection
