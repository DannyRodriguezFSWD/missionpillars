@extends('layouts.public')

@section('content')

<div class="container">
    @if(count($events) <= 0)
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-4">
                    <h1>@lang('No events found')</h1>
                    <h5>@lang('Change date range and try again')</h5>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row mt-4">
        @if(count($calendars) > 1)
        <div class="col-sm-12 col-md-3 mb-2">
                @if(is_null($showing))
                <button id="show-calendar" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @lang('Show all calendars')
                </button>
                @else
                <button id="show-calendar" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: {{ array_get($showing, 'color') }}">
                    {{ array_get($showing, 'name') }}
                </button>
                @endif
                
                <div class="dropdown-menu" x-placement="bottom-start">
                    <a class="dropdown-item calendar-option" href="{{ route('calendar.shareCalendarListMode', ['id' => $id, 'calendars' => implode('-', array_pluck($calendars, 'id'))]) }}">
                        @lang('Show all calendars')
                    </a>
                    @foreach( $calendars as $calendar )
                    <button class="dropdown-item calendar-option" data-show="{{ array_get($calendar, 'id') }}">
                        <i class="fa fa-square" style="color: {{ array_get($calendar, 'color') }}"></i>
                        {{ array_get($calendar, 'name') }}
                    </button>
                    @endforeach
                </div>
        </div>
        @endif
        <div class="col-sm-12 col-md-6 mb-2">
            {{ Form::open(['route' => ['calendar.shareCalendarListMode', $id], 'class' => 'form-inline', 'method' => 'GET']) }}
            {{ Form::hidden('calendars', implode('-', array_pluck($calendars, 'id'))) }}
            {{ Form::hidden('show', array_get($showing, 'id')) }}
            <div>
                {{ Form::select('month', App\Constants::MONTHS, $month, ['class' => 'form-control']) }}
            </div>
            <div class="ml-4">
                {{ Form::select('year', $years, $year, ['class' => 'form-control']) }}
            </div>
            {{ Form::close() }}
        </div>
    </div>

    @foreach($events as $event)
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header px-3 py-2" style="font-size: 1.5em; background-color: {{ array_get($event, 'template.calendar.color') }}">
                    <i class="fa fa-calendar"></i>
                    <span class="text-uppercase">{{ array_get($event, 'template.name') }}</span>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="text-uppercase">
                                <i class="fa fa-clock-o"></i>
                                @if( array_get($event, 'template.is_all_day') )
                                {{ \Carbon\Carbon::parse(array_get($event, 'start_date'))->toFormattedDateString() }}
                                @else
                                {{ displayLocalDateTime(array_get($event, 'start_date'))->toFormattedDateString() }}
                                
                                {{ displayLocalDateTime(array_get($event, 'start_date'))->toTimeString() }}
                                -
                                {{ displayLocalDateTime(array_get($event, 'end_date'))->toTimeString() }}
                                @endif
                            </p>
                            @if(array_get($event, 'template.allow_reserve_tickets'))
                            <p class="text-uppercase">
                                <i class="fa fa-info-circle text-primary"></i>
                                @lang('This event requires ticket reservation')
                            </p>
                            @endif
                            @if(!is_null(array_get($event, 'template.addressInstance.0')))
                            <div class="text-uppercase">
                                <i class="fa fa-home"></i>
                                {{ array_get($event, 'template.addressInstance.0.mailing_address_1') }}
                            </div>
                            <div class="text-uppercase">
                                <i class="fa fa-home text-white"></i>
                                {{ array_get($event, 'template.addressInstance.0.city') }}
                            </div>
                            <div class="text-uppercase">
                                <i class="fa fa-home text-white"></i>
                                {{ array_get($event, 'template.addressInstance.0.region') }}
                            </div>
                            <div class="text-uppercase">
                                <i class="fa fa-home text-white"></i>
                                {{ array_get($event, 'template.addressInstance.0.countries.name') }}
                            </div>
                            @endif
                            @if(!is_null(array_get($event, 'template.description')))
                            <p>
                                <br/>
                                <small class="text-uppercase">
                                    <i class="fa fa-comment"></i>
                                    {{ array_get($event, 'template.description') }}
                                </small>
                            </p>
                            @endif
                        </div>
                        @if(array_get($event, 'template.is_paid'))
                        <div class="col-sm-6 text-uppercase">
                            <p><i class="fa fa-ticket"></i> Ticket Options</p>
                            <div style="max-height: 225px; overflow: auto; border: 1px solid rgba(0, 0, 0, 0.125)">
                                <ul class="list-group">
                                    @foreach(array_get($event, 'template.ticketOptions') as $option)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ array_get($option, 'name') }}
                                        <span class="badge badge-primary badge-pill p-2">${{ array_get($option, 'price') }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
                <div class="card-footer px-3 py-2 text-right">
                    @if(array_get($event, 'template.allow_auto_check_in') && !array_get($event, 'template.allow_reserve_tickets'))
                            @php
                            $caption = 'Check in';
                            @endphp
                        @else
                            @php
                            $caption = 'Register now';
                            @endphp
                        @endif
                    @include('shared.sessions.submit-button', ['start_url' => request()->fullUrl(), 'next_url' => route('events.share', ['id' => $event->uuid]), 'caption' => $caption, 'form' => true])
                    
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script type="text/javascript">
    (function(){
        $('.form-control').on('change', function(e){
            $(this).parents('.form-inline').submit();
        });
        
        $('.calendar-option').on('click', function(e){
            var show = $(this).data('show');
            var form = $('form.form-inline');
            var input = form.find('input[name="show"]').val(show);
            form.submit();
        });
    })();
</script>
@endpush()

@endsection
