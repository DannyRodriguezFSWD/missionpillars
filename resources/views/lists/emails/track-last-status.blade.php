@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="mb-0">{{ $total }}</h4>
                <p>@lang('Sent Emails')</p>
                <h3>{{ array_get($email, 'subject') }}</h3>
                <div class="btn-group btn-group" role="group" aria-label="...">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-cog"></i>
                            @lang('Settings')
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-left">
                            <a class="dropdown-item" href="{{ route('lists.edit', ['id' => array_get($list, 'id'), 'email' => array_get($email, 'id')]) }}">
                                <span class="fa fa-paper-plane"></span>
                                @lang('Send again')
                            </a>
                            <button class="dropdown-item" data-toggle="modal" data-target="#delete-email-modal">
                                <span class="fa fa-trash"></span>
                                @lang('Delete')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <canvas id="chart"></canvas>
            </div>
        </div>

    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>@lang('Contact')</th>
                <th>@lang('Email')</th>
                <th>@lang('Status')</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sentOut as $sent)
                <tr class="clickable-row" data-href="{{ route('lists.email.track.history', ['list' => array_get($list, 'id'), 'email' => array_get($email, 'id'), 'track' => array_get($sent, 'id')]) }}">
                    <td>{{ array_get($sent, 'contact.first_name') }} {{ array_get($sent, 'contact.last_name') }}</td>
                    <td>{{ array_get($sent, 'contact.email_1') }}</td>
                    <td>
                        @if( in_array(array_get($sent, 'status'), ['accepted', 'sent']) )
                            <span class="badge badge-pill badge-info p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['delivered']) )
                            <span class="badge badge-pill badge-primary p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['opened', 'clicked']) )
                            <span class="badge badge-pill badge-success p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['unsubscribed', 'complained']) )
                            <span class="badge badge-pill badge-warning p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['rejected', 'failed', 'error']) )
                            <span class="badge badge-pill badge-danger p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @else
                            <span class="badge badge-pill badge-default p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @endif
                    </td>
                    <td class="text-right"><span class="icon icon-arrow-right"></span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-body">
        {{ $sentOut->links() }}
    </div>
    <div class="card-footer">&nbsp;</div>
</div>
@include('emails.includes.delete-modal')

@push('scripts')
<script type="text/javascript">
    (function () {
    var canvas = $('#chart');
            var chart = new Chart(canvas, {
            type: 'pie',
                    data: {!! json_encode($chart) !!},
                    options: {
                    responsive: true
                    }
            });
    }
    )();
</script>
@endpush

@endsection
