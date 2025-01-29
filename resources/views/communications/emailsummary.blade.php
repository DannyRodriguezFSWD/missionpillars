@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('communications.emailsummary',$email) !!}
@endsection
@section('title')
    Email Commmunication Summary
@endsection
@section('content')

    <div class="card">
        <div class="card-header">
            @include('widgets.back')
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <h1>Email Communication Summary</h1>
                    <h3>{{ array_get($email, 'subject') }}</h3>
                </div>
                <div class="col-sm-6">
                    <p>
                    <h4 class="mb-0">{{ $total }} Sent {{ str_plural('Email',$total) }}</h4>
                    for {{ $totalcontacts }} unique  {{ str_plural('contact', $totalcontacts) }}
                    </p>
                    <p>
                    <div class="btn-group btn-group" role="group" aria-label="...">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fa fa-cog"></i>
                                @lang('Settings')
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-left">
                                <a class="dropdown-item"
                                   href="{{ route('communications.configureemail', ['id' => array_get($email, 'id')]) }}">
                                    <i class="fa fa-paper-plane"></i>&nbsp;@if($total)Resend @else Send @endif
                                </a>
                                <a class="dropdown-item"
                                   href="{{ route('communications.edit', ['id' => array_get($email, 'id')]) }}">
                                    <i class="fa fa-paper-plane"></i>&nbsp;Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group status-list">
                                <li data-status="" class="list-group-item list-group-item-action" role="button">All<span
                                            class="badge badge-dark float-right">{{$totalCount}}</span></li>
                                <li data-status="Queued" class="list-group-item list-group-item-action" role="button">
                                    Queued<span class="badge-secondary badge float-right">{{$statusTotal['Queued']['total']}} ({{ round(($statusTotal['Queued']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="sent" class="list-group-item list-group-item-action" role="button">
                                    Sent<span class="badge-info badge float-right">{{$statusTotal['sent']['total'] + $statusTotal['delivered']['total'] + $statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total'] + $statusTotal['failed']['total'] + $statusTotal['rejected']['total']}} ({{ round(($statusTotal['sent']['total'] + $statusTotal['delivered']['total'] + $statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total'] + $statusTotal['failed']['total'] + $statusTotal['rejected']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="delivered" class="list-group-item list-group-item-action" role="button">
                                    Delivered<span class="badge-primary badge float-right">{{$statusTotal['delivered']['total'] + $statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total']}} ({{ round(($statusTotal['delivered']['total'] + $statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="opened" class="list-group-item list-group-item-action" role="button">
                                    Opened<span class="badge-success badge float-right">{{$statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total']}} ({{ round(($statusTotal['opened']['total'] + $statusTotal['clicked']['total'] + $statusTotal['unsubscribed']['total'] + $statusTotal['complained']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="clicked" class="list-group-item list-group-item-action" role="button">
                                    Clicked <span class="badge-success badge float-right">{{$statusTotal['clicked']['total']}} ({{ round(($statusTotal['clicked']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group status-list">
                                <li data-status="unsubscribed" class="list-group-item list-group-item-action" role="button">
                                    Unsubscribed <span
                                            class="badge-warning badge float-right">{{$statusTotal['unsubscribed']['total']}} ({{ round(($statusTotal['unsubscribed']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="complained" class="list-group-item list-group-item-action" role="button">
                                    Complained <span class="badge-warning badge float-right">{{$statusTotal['complained']['total']}} ({{ round(($statusTotal['complained']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="error" class="list-group-item list-group-item-action" role="button">
                                    Error <span class="badge-danger badge float-right">{{$statusTotal['error']['total']}} ({{ round(($statusTotal['error']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="failed" class="list-group-item list-group-item-action" role="button">
                                    Failed <span class="badge-danger badge float-right">{{$statusTotal['failed']['total']}} ({{ round(($statusTotal['failed']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                                <li data-status="rejected" class="list-group-item list-group-item-action" role="button">
                                    Rejected <span class="badge-danger badge float-right">{{$statusTotal['rejected']['total']}} ({{ round(($statusTotal['rejected']['total']) / ($totalCount === 0 ? 1 : $totalCount) * 100) }}%)</span></li>
                            </ul>
                        </div>
                    </div>
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
                </tr>
                </thead>
                <tbody>
                @foreach($sentOut as $sent)
                    <tr class="clickable-row"
                        data-href="{{ route('communications.email.track.history', ['email' => array_get($email, 'id'), 'track' => array_get($sent, 'id')]) }}">
                        <td>{{ array_get($sent, 'contact.first_name') }} {{ array_get($sent, 'contact.last_name') }}</td>
                        <td>{{ array_get($sent, 'contact.email_1') }}</td>
                        <td>
                            @php
                                $badgetype = 'badge-secondary';
                                if( in_array(array_get($sent, 'status'), ['sent']) )
                                    $badgetype = 'badge-info';
                                elseif( in_array(array_get($sent, 'status'), ['delivered']) )
                                    $badgetype = 'badge-primary';
                                elseif( in_array(array_get($sent, 'status'), ['opened', 'clicked']) )
                                    $badgetype = 'badge-success';
                                elseif( in_array(array_get($sent, 'status'), ['unsubscribed', 'complained']) )
                                    $badgetype = 'badge-warning';
                                elseif( in_array(array_get($sent, 'status'), ['rejected', 'failed', 'error']) )
                                    $badgetype = 'badge-danger';
                            @endphp
                            <span class="badge badge-pill {{ $badgetype }} p-2">
                            @if (array_get($email, 'time_scheduled') && strtotime(array_get($email, 'time_scheduled')) > strtotime(date('Y-m-d H:i:s')) && !array_get($sent, 'sent'))
                                Scheduled for {{ displayLocalDateTime(array_get($email, 'time_scheduled'))->format('n/j/Y g:i A') }}
                            @else
                                {{ title_case(array_get($sent, 'status')) }} {{ date("n/j/Y", strtotime($sent->sent_at)) }}
                            @endif
                    </span>
                        </td>
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
@endsection
@push('scripts')
    <script>
        const status = '{{$status}}'
        const selectedEL = document.querySelector(`.status-list > .list-group-item-action[data-status="${status}"]`) || document.querySelector(`.status-list > .list-group-item-action[data-status="all"]`);
        if (selectedEL) selectedEL.classList.add('active');
        $('.status-list > .list-group-item-action').click(function (e) {
            const el = e.target;
            window.location.href = '{{route('communications.emailsummary',['id' => $email->id])}}' + `/${el.getAttribute('data-status')}`
        })
    </script>
@endpush