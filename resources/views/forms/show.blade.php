@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('forms.edit',$form) !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-12 text-center">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> Back
                </a>
                {{ array_get($form, 'name') }}
            </div>
        </div>
    </div>  
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link connected @if($tab === 'connected' || !$tab) active @endif" data-toggle="tab" href="#connected" role="tab" aria-controls="profile">
                    <i class="icon-link"></i> @lang('Linked Form Entries')
                    @if($totalConnected > 0)
                    <span class="badge badge-info badge-pill">{{ $totalConnected }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link entries @if($tab === 'entries') active @endif" data-toggle="tab" href="#entries" role="tab" aria-controls="entries">
                    <i class="icon-star"></i> @lang('Not Linked Form Entries')
                    @if($total > 0)
                    <span class="badge badge-info badge-pill">{{ $total }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        
        <div class="tab-content">
            <div class="tab-pane @if($tab === 'connected' || !$tab) active @endif" id="connected" role="tabpanel">
                <h4 class="mb-0">{{ $totalConnected }}</h4>
                <p>@lang('Linked Form Entries')</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Status')</th>
                                @if (array_get($form, 'accept_payments'))
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Payment status')</th>
                                @endif
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($linked as $entry)
                            @php
                            $e = json_decode(array_get($entry, 'json', '{}'), true);
                            @endphp
                            <tr class="clickable-row" data-href="{{ route('entries.show', ['id' => array_get($entry, 'id')]) }}">
                                <td>
                                    {{ array_get($entry, 'contact.0.first_name') }}
                                    {{ array_get($entry, 'contact.0.last_name') }}
                                </td>
                                <td>{{ displayLocalDateTime(array_get($entry, 'created_at'))->toDayDateTimeString() }}</td>
                                <td>
                                    @lang('Connected')
                                </td>
                                @if (array_get($form, 'accept_payments'))
                                <td class="text-right">
                                    @if(array_get($entry, 'transaction.status') == 'complete')
                                        <span class="text-success">${{ number_format(array_get($entry, 'transaction.splits.0.amount', 0), 2) }}</span>
                                    @else
                                        <span class="text-danger">${{ number_format(array_get($e, 'total', 0), 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(array_get($entry, 'transaction.status') == 'complete')
                                        <span class="text-success">@lang('Paid')</span>
                                    @elseif(array_get($entry, 'transaction.status') == 'pending')
                                        <span class="text-success">@lang('Pending')</span>
                                    @else
                                    <span class="text-danger">@lang('Not Paid')</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    {{ $linked->appends(['tab' => 'connected', 'unlinked' => $unlinked->currentPage()])->links() }}
                </div>
            </div>
            <div class="tab-pane @if($tab === 'entries') active @endif" id="entries" role="tabpanel">
                <h4 class="mb-0">{{ $total }}</h4>
                <p>@lang('Not Linked Form Entries')</p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Status')</th>
                                @if (array_get($form, 'accept_payments'))
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Payment status')</th>
                                @endif
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unlinked as $entry)
                            @php
                            $e = json_decode(array_get($entry, 'json', '[]'), true);
                            @endphp
                            <tr class="entries-show" data-href="{{ route('entries.show', ['id' => array_get($entry, 'id'), 'tab' => 'entries']) }}">
                                <td>
                                    {{ array_get($e, 'first_name') }}
                                    {{ array_get($e, 'last_name') }}
                                </td>
                                <td>{{ date('M d, Y g:i:s A', strtotime($entry->created_at)) }}</td>
                                <td>
                                    @lang('Could Not Match') (<a href="{{ route('entries.show', ['id' => $entry->id]) }}" style="display: inline;">@lang('connect')</a>)
                                </td>
                                @if (array_get($form, 'accept_payments'))
                                <td class="text-right">
                                    @if(!is_null(array_get($entry, 'transaction_id')))
                                        <span class="text-success">${{ number_format(array_get($e, 'total', 0), 2) }}</span>
                                    @else
                                        <span class="text-danger">${{ number_format(array_get($e, 'total', 0), 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!is_null(array_get($entry, 'transaction_id')))
                                        <span class="text-success">@lang('Paid')</span>
                                    @else
                                        <span class="text-danger">@lang('Not Paid')</span>
                                    @endif
                                </td>
                                @endif
                                <td>
                                    @include('forms.includes.delete-entry')
                                </td>
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div>
                    {{ $unlinked->appends(['tab' => 'entries', 'linked' => $linked->currentPage()])->links() }}
                </div>
            </div>
        </div>
        
    </div>
</div>

@include('forms.includes.delete-entry-modal')

@push('scripts')
<script>
    $('.entries-show').click(function (e) {
        if (!$(e.target).parents().hasClass('delete-entry')) {
            window.location = $(this).data("href");
        }
    });
</script>
@endpush
@endsection
