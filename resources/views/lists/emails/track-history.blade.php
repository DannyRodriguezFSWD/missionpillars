@extends('layouts.app')

@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.email.tracking',$email) !!}
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        @if($list)
            <h4>@lang('List:') <small>{{ array_get($list, 'name') }}</small></h4>
        @endif
        <h4>@lang('Email:') <small>{{ array_get($email, 'subject') }}</small></h4>
        <h4>
            @lang('Sent to:')
            <small>
                {{ array_get($sent->contact, 'first_name') }}
                {{ array_get($sent->contact, 'last_name') }}
                ({{ array_get($sent->contact, 'email_1') }})
            </small>
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>@lang('Status')</th>
                <th>@lang('Timestamp')</th>
                <th>@lang('Log')</th>
                <th>@lang('Severity')</th>
                <th>@lang('Reason')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sent->track()->orderBy('status_timestamp', 'desc')->get() as $track)
                <tr>
                    <td>
                        @if( in_array(array_get($track, 'status'), ['accepted', 'sent', 'resubscribed']) )
                            <span class="badge badge-pill badge-info p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($track, 'status'), ['delivered']) )
                            <span class="badge badge-pill badge-primary p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($track, 'status'), ['opened', 'clicked']) )
                            <span class="badge badge-pill badge-success p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($track, 'status'), ['unsubscribed', 'complained']) )
                            <span class="badge badge-pill badge-warning p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($track, 'status'), ['rejected', 'failed', 'error']) )
                            <span class="badge badge-pill badge-danger p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @else
                            <span class="badge badge-pill badge-default p-2">
                        {{ title_case(array_get($track, 'status')) }}
                    </span>
                        @endif
                    </td>
                    <td>{{ array_get($track, 'status_timestamp') }}</td>
                    <td>{{ array_get($track, 'log_level') }}</td>
                    <td>{{ array_get($track, 'severity') }}</td>
                    <td>{{ array_get($track, 'reason') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">&nbsp;</div>
</div>

@include('lists.includes.delete-modal')

@endsection
