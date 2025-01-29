@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.settings',$event) !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header text-center">
        @include('widgets.back')
        <div class="d-inline h4">{{ $event->name }}</div>       
    </div>
    
    <div class="card-body">
        <div class="row">
            @include('events.includes.event-settings-menu')
            <div class="col-md-7 vertical-menu-bar">
                <h4>{{$total}} @lang('People attending event')</h4>
                <div class="table-responsive">
                    <table class="table table-responsive">
                        <thead>
                        <tr>
                            {{-- <th>&nbsp;</th> --}}
                            <th>@lang('Name')</th>
                            <th class="text-center">@lang('Checked in')</th>
                            @if( !is_null($event->linkedForm) )
                                <th class="text-center">@lang('Completed form')</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($contacts as $contact)
                            <tr>
                                {{-- <td><input type="checkbox" name="contacts[]" value="{{ array_get($contact, 'id') }}"/></td> --}}
                                <td>
                                    <h6>{{ array_get($contact, 'first_name') }} {{ array_get($contact, 'last_name') }}</h6>
                                    <h6 class="text-muted">
                                        <small>@lang('Registered on') {{ displayLocalDateTime(array_get($contact, 'pivot.created_at'))->toDayDateTimeString() }}</small>
                                    </h6>
                                </td>
                                <td class="text-center">
                                    @if( in_array(array_get($contact, 'id'), $attendersWithEventCheckIn) )
                                        <strong><span class="text-success">@lang('YES')</span></strong>
                                    @else
                                        <strong><span class="text-danger">@lang('NO')</span></strong>
                                    @endif
                                </td>
                                @if( !is_null($event->linkedForm) )
                                    <td class="text-center">

                                        @if( in_array(array_get($contact, 'id'), $attendersWithEventFormEntry) )
                                            <strong><span class="text-success">@lang('YES')</span></strong>
                                        @else
                                            <strong><span class="text-danger">@lang('NO')</span></strong>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @include('events.includes.event-people-action-menu', ['event_id'=>$event->id])
        </div>
    </div>
</div>
@include('events.includes.actions-event-modal')
@endsection
