@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('sms.show',$sms) !!}
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="mb-0">{{ $total }}</h4>
                @if (array_get($sms, 'is_scheduled'))
                <p>@lang('Scheduled SMS for') {{ displayLocalDateTime(array_get($sms, 'time_scheduled'))->format("n/d/Y g:i a") }}</p>
                @else
                <p>@lang('Sent SMS')</p>
                @endif
                <h3>{{ array_get($sms, 'content') }}</h3>
                <!--
                <div class="btn-group btn-group" role="group" aria-label="...">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-cog"></i>
                            @lang('Settings')
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-left">
                            <button class="dropdown-item" data-toggle="modal" data-target="#delete-email-modal">
                                <span class="fa fa-trash"></span>
                                @lang('Delete')
                            </button>
                        </div>
                    </div>
                </div>
            -->
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
                <th>@lang('Phone Number')</th>
                <th>@lang('Status')</th>
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sentOut as $sent)
                <tr class="clickable-row" data-href="{{ route('sms.track.history', ['sms' => array_get($sms, 'id'), 'track' => array_get($sent, 'id')]) }}">
                    <td>{{ array_get($sent, 'to.first_name') }} {{ array_get($sent, 'to.last_name') }}</td>
                    <td>{{ array_get($sent, 'to.cell_phone') }}</td>
                    <td>
                        @if( in_array(array_get($sent, 'status'), ['accepted', 'sent']) )
                            <span class="badge badge-pill badge-info p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['delivered']) )
                            <span class="badge badge-pill badge-success p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['rejected', 'failed', 'error']) )
                            <span class="badge badge-pill badge-danger p-2">
                        {{ title_case(array_get($sent, 'status')) }}
                    </span>
                        @elseif( in_array(array_get($sent, 'status'), ['Queued']) && array_get($sms, 'is_scheduled') )
                            <span class="badge badge-pill badge-info p-2">
                        Scheduled
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
@include('communications.sms.includes.delete-modal')

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

    (function (){
        let url = new URL(window.location.href);
        if (url.searchParams.get('created')) Swal.fire("Success!", 'Your message has been queued. You can recheck this page at any time to see the status of individual messages.', "success")
    })()
</script>
@endpush

@endsection
