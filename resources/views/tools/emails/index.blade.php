@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('tools.email.viewer') !!}
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="text-center">
            <a href="javascript:history.back()" class="pull-left">
                <span class="fa fa-chevron-left"></span>
                Back
            </a>
            @lang('Email Viewer')
        </div>
    </div>
    
    @if (!is_null($email_sent))
    <div class="card-body">
        <p>@lang('To'): {{ array_get($email_sent, 'contact.email_1') }}</p>
        <p>@lang('Subject'): {{ array_get($email_sent, 'content.subject') }}</p>
        <p>
            @lang('Status'): 
            @if (array_get($email_sent, 'status') == 'sent')
            <td>
                <span class="badge badge-pill badge-success p-2">{{ array_get($email_sent, 'status') }}</span>
            </td>
            @elseif (strtolower(array_get($email_sent, 'status')) == 'queued')
            <td>
                <span class="badge badge-pill badge-info p-2">{{ array_get($email_sent, 'status') }}</span>
            </td>
            @else
            <td>
                <span class="badge badge-pill badge-danger p-2">{{ array_get($email_sent, 'status') }}</span>
            </td>
            @endif
        </p>
        <p>@lang('Sent at'): {{ array_get($email_sent, 'sent_at') }}</p>
    </div>
    
    <div class="card-body">
        <hr>
        {!! array_get($email_sent, 'content.content') !!}
    </div>
    @else
    <table class="table table-hover">
        <thead>
            <tr>
                <th>@lang('Sent to')</th>
                <th>@lang('Subject')</th>
                <th>@lang('Status')</th>
                <th>@lang('Sent at')</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emails as $email)
            <tr class="clickable-row" data-href="{{ route('tools.email.viewer', ['id' => array_get($email, 'id')]) }}">
                <td>{{ array_get($email, 'contact.email_1') }}</td>
                <td>{{ array_get($email, 'content.subject') }}</td>
                @if (array_get($email, 'status') == 'sent')
                <td>
                    <span class="badge badge-pill badge-success p-2">{{ array_get($email, 'status') }}</span>
                </td>
                @elseif (strtolower(array_get($email, 'status')) == 'queued')
                <td>
                    <span class="badge badge-pill badge-info p-2">{{ array_get($email, 'status') }}</span>
                </td>
                @else
                <td>
                    <span class="badge badge-pill badge-danger p-2">{{ array_get($email, 'status') }}</span>
                </td>
                @endif
                <td>{{ array_get($email, 'sent_at') }}</td>
                <td class="text-right"><span class="icon icon-arrow-right"></span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="card-body">
        {{ $emails->links() }}
    </div>
    @endif
    
    <div class="card-footer">&nbsp;</div>
</div>


@endsection