@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <h4>@lang('List:') <small>{{ array_get($list, 'name', 'Everyone') }}</small></h4>
        <h4>@lang('SMS:') <small>{{ array_get($sms, 'content') }}</small></h4>
        <h4>
            @lang('Sent to:') 
            <small>
                {{ array_get($sent, 'contact.first_name') }}
                {{ array_get($sent, 'contact.last_name') }}
                ({{ array_get($sent, 'contact.cell_phone') }})
            </small>
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Status')</th>
                <th>@lang('Timestamp')</th>
                <th>@lang('Message')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($history as $record)
                <tr>
                    <td>
                        @if( in_array(array_get($record, 'status'), ['accepted', 'sent']) )
                            <span class="badge badge-pill badge-info p-2">
                        {{ title_case(array_get($record, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($record, 'status'), ['delivered']) )
                            <span class="badge badge-pill badge-success p-2">
                        {{ title_case(array_get($record, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($record, 'status'), ['rejected', 'failed', 'error']) )
                            <span class="badge badge-pill badge-danger p-2">
                        {{ title_case(array_get($record, 'status')) }}
                    </span>
                        @else
                            <span class="badge badge-pill badge-default p-2">
                        {{ title_case(array_get($record, 'status')) }}
                    </span>
                        @endif
                    </td>
                    <td>{{ array_get($record, 'created_at') }}</td>
                    <td>{{ array_get($record, 'message') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="card-footer">&nbsp;</div>
</div>




@endsection
