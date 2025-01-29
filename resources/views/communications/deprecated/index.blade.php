{{-- DEPRECATED see ../index.php --}}
@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">&nbsp;</div>
    <div class="card-body">
        <div class="btn-group btn-group" role="group" aria-label="...">
            <div class="input-group-btn">
                <a href="{{ route('communications.show', ['id' => 'message']) }}" class="btn btn-primary">
                    <i class="fa fa-commenting-o"></i>
                    @lang('Send New Mass Message')
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
                <th>&nbsp;</th>
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
                            {{ array_get($message, 'type') }}
                        </td>
                        <td>
                            {{ array_get($message, 'created_at') }}
                        </td>
                        <th class="text-right">
                            <span class="icon icon-arrow-right"></span>
                        </th>
                    </tr>
                    @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $messages->links() }}</div>
    <div class="card-footer">&nbsp;</div>
</div>





@endsection
