@extends('layouts.auth-forms')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card text-white bg-success">
                <div class="card-header" style="background-color: transparent !important;">
                    <h3>{{ array_get($ticket, 'registry.event.template.name') }}</h3>
                </div>
                <div class="card-body p-4">
                    <p>
                        @lang("Hello") {{ array_get($ticket, 'registry.contact.first_name') }} {{ array_get($ticket, 'registry.contact.last_name') }}
                        @lang("If you agree to use this ticket you won't be able to use it twice")
                    </p>
                    <p>
                        <strong>@lang('Ticket number'):</strong>
                        {{ array_get($ticket, 'id') }}<br/>
                        <strong>@lang('Ticket type'):</strong>
                        {{ array_get($ticket, 'ticket_name') }}
                    </p>
                    <p>
                        <strong>@lang('Event'):</strong>
                        {{ array_get($ticket, 'registry.event.template.name') }}
                    </p>
                    <p>
                        <strong>@lang('Date'):</strong>
                        @if(array_get($ticket, 'registry.event.template.is_all_day'))
                        {{ humanReadableDate(displayLocalDateTime(array_get($ticket, 'registry.event.start_date'), array_get($ticket, 'registry.event.template.timezone'))) }}
                        @else
                        {{ displayLocalDateTime(array_get($ticket, 'registry.event.start_date'), array_get($ticket, 'registry.event.template.timezone'))->toDayDateTimeString() }}
                        -
                        {{ displayLocalDateTime(array_get($ticket, 'registry.event.end_date'), array_get($ticket, 'registry.event.template.timezone'))->toDayDateTimeString() }}
                        @endif
                    </p>
                    <p><strong>@lang('Address'):</strong></p>
                    <p>
                        {{ array_get($ticket, 'registry.event.template.addressInstance.0.mailing_address_1') }}<br/>
                        {{ array_get($ticket, 'registry.event.template.addressInstance.0.city') }}<br/>
                        {{ array_get($ticket, 'registry.event.template.addressInstance.0.region') }}<br/>
                        {{ array_get($ticket, 'registry.event.template.addressInstance.0.countries.name') }}
                    </p>
                </div>
                <div class="card-footer" style="background-color: transparent !important;">
                    <div class="row">
                        <div class="col-sm-6 text-right">
                            {{ Form::open(['route' => 'qr.store']) }}
                            {{ Form::hidden('id', array_get($ticket, 'id')) }}
                            {{ Form::hidden('action', $action) }}
                            <button type="submit" class="btn btn-danger">@lang('Go Ahead')</button>
                            {{ Form::close() }}
                        </div>
                        <div class="col-sm-6">
                            <a href="{{ route('events.share', ['id' => array_get($ticket, 'registry.event.uuid')]) }}" class="btn btn-secondary">@lang('Cancel')</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
