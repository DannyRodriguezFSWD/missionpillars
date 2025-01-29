@extends('layouts.auth-forms')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-8">
            <div class="card text-white bg-danger">
                <div class="card-header" style="background-color: transparent !important;">
                    <h3>{{ array_get($ticket, 'registry.event.template.name') }}</h3>
                </div>
                <div class="card-body p-4">
                    <p>
                        @lang("Hello") {{ array_get($ticket, 'registry.contact.first_name') }} {{ array_get($ticket, 'registry.contact.last_name') }}
                        @lang("this ticket was already used at")
                        {{ displayLocalDateTime(array_get($ticket, 'used_at'))->toDayDateTimeString() }}
                    </p>
                    <p>
                        <strong>@lang('Ticket number'):</strong>
                        {{ array_get($ticket, 'id') }}<br/>
                        <strong>@lang('Ticket type'):</strong>
                        {{ array_get($ticket, 'ticket_name') }}
                    </p>
                </div>
                <div class="card-footer" style="background-color: transparent !important;">
                    <div class="row">
                        <div class="col-sm-12 text-right">
                            <a href="{{ route('events.share', ['id' => array_get($ticket, 'registry.event.uuid')]) }}" class="btn btn-secondary">@lang('Ok')</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
