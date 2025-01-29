@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('sms.index') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <div class="btn-group btn-group" role="group" aria-label="...">
            <div class="input-group-btn">
                <a href="{{ route('sms.create', ['id' => 'message']) }}" class="btn btn-primary">
                    <i class="fa fa-commenting-o"></i>
                    @lang('Send New SMS Message')
                </a>
            </div>
        </div>
    </div>
    <main id="mass-message-history">

    </main>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Subject')</th>
                <th>@lang('Sent to list')</th>
                <th>@lang('Type')</th>
                <th>@lang('Created at')</th>
                <th>@lang('# Contacts')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($messages as $message)
                @if (array_get($message, 'type') == 'SMS')
                    <tr class="clickable-row" data-href="{{ route('sms.show', ['id' => array_get($message, 'id')]) }}">
                @else
                    <tr class="clickable-row" data-href="{{ route('emails.show', ['id' => array_get($message, 'id')]) }}">
                        @endif

                        <td>
                            {{ array_get($message, 'content') }}
                        </td>
                        <td>
                            @if (empty(array_get($message, 'list_name')))
                                @lang('Everyone')
                            @else
                                {{ array_get($message, 'list_name') }}
                            @endif
                        </td>
                        <td>
                    <span class="badge badge-pill badge-primary">
                        {{ array_get($message, 'type') }}
                    </span>
                        </td>
                        <td>
                            {{ array_get($message, 'created_at')->format("n/d/Y g:m a") }}
                        </td>
                        <td>
                            {{ $message->sent_count }}
                        </td>
                    </tr>
                    @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $messages->links() }}</div>
    <div class="card-footer">&nbsp;</div>
</div>





@endsection
