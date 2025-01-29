@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('events.settings',$event) !!}
@endsection
@section('content')

<div class="card no-print">
    <div class="card-header text-center">
        @include('widgets.back')
        <div class="d-inline h4">{{ $event->name }}</div>
    </div>
    
    <div class="card-body">
        <div class="row">
            @include('events.includes.event-settings-menu')
            <div class="col-md-10 vertical-menu-bar">
                <div class="row">
                    <div class="col-sm-12">
                        <h4>@lang('Report for people who checked in this event')</h4>
                        <div class="pull-right">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false"> @lang('Export')
                                    <span class="caret"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="javascript:window.print()">
                                        <span class="fa fa-print"></span> @lang('Print Attendance Sheet')
                                    </a>
                                    <a class="dropdown-item" target="_blank" href="{{ route('events.export.excel', ['id' => $split->id]) }}">
                                        <span class="fa fa-table"></span> @lang('Download as Excel')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    @foreach($repetitions as $repeat)
                                        <th class="text-center">
                                            <small>{{ humanReadableDate(displayLocalDateTime(array_get($repeat, 'start_date'))->toDateString()) }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($contacts as $contact)
                                    <tr>
                                        <td>
                                            <small>{{ $contact->first_name }} {{ $contact->last_name }}</small>
                                        </td>
                                        @foreach($repetitions as $repeat)
                                            @if( !is_null($contact->checkedIn()->where('calendar_event_template_split_id', array_get($split, 'id'))->first()) )
                                                <td class="text-center text-success">
                                                    <span class="icon icon-check"></span>
                                                </td>
                                            @else
                                                <td class="text-center text-danger">
                                                    <span class="icon icon-close"></span>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@include('events.includes.actions-event-modal')
@includeIf('events.includes.print-report-view')

@endsection
