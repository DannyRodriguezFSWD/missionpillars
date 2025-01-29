@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('recurring.show',$template) !!}
@endsection
@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                

                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong>@lang('Amount'):</strong>
                                <span class="badge badge-pill badge-primary p-2">$ {{ array_get($template, 'amount') }}</span>
                                @if( array_get($template, 'is_recurring') )
                                    {{ array_get($template, 'billing_period', '?') }}
                                    @lang('during') {{array_get($template, 'billing_cycles')}}
                                    {{ array_get($periods, array_get($template, 'billing_period', '?'), '?') }}{{ array_get($template, 'billing_cycles') > 1 ? 's':'' }}
                                @endif
                            </td>
                        </tr>
                        @if( array_get($template, 'is_recurring') )
                        <tr>
                            <td>
                                <strong>@lang('Total at the end of recurring payments'):</strong>
                                <!-- <span class="badge badge-pill badge-primary p-2">$ {{ number_format((array_get($template, 'amount') * (array_get($template, 'billing_cycles', 0) - count($splits))) + $splits->sum('amount'), 2) }}</span> -->
                                <span class="badge badge-pill badge-primary p-2">$ {{ number_format(array_get($template, 'amount') * array_get($template, 'billing_cycles'), 2) }}</span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td>
                                <strong>@lang('Received'):</strong> <span class="badge badge-pill badge-success p-2">$ {{ number_format($sum, 2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Promised Pay Date'):</strong> {{ humanReadableDate(array_get($template, 'billing_start_date', \Carbon\Carbon::now())) }}
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong>@lang('From'):</strong> {{ array_get($template, 'contact.first_name') }} 
                                {{ array_get($template, 'contact.last_name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Email'):</strong> {{ array_get($template, 'contact.email_1') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Purpose'):</strong> {{ array_get($template_split, 'purpose.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Fundraiser'):</strong> {{ array_get($template_split, 'campaign.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Status'):</strong> 
                                @includeIf('recurring_transactions.includes.status-color-indicator')
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>@lang('Date')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Status')</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tbody>
                        @foreach( $splits as $split )
                            <tr class="clickable-row" data-href="{{ route('transactions.edit', ['id' => array_get($split, 'id'), 'ur' => 'false']) }}">
                                <td>{{ humanReadableDate( array_get($split, 'transaction.transaction_initiated_at', \Carbon\Carbon::now())) }}</td>
                                <td>$ {{ array_get($split, 'amount') }}</td>
                                <td>
                                    @if(array_get($split, 'transaction.status') === 'complete')
                                        <span class="badge badge-pill badge-success p-2">
                                        {{ array_get($split, 'transaction.status') }}
                                    </span>
                                    @elseif(array_get($split, 'transaction.status') === 'fail')
                                        <span class="badge badge-pill badge-danger p-2">
                                        {{ array_get($split, 'transaction.status') }}
                                    </span>
                                    @elseif(array_get($split, 'transaction.status') === 'pending')
                                        <span class="badge badge-pill badge-warning p-2">
                                        {{ array_get($split, 'transaction.status') }}
                                    </span>
                                    @else
                                        <span class="badge badge-pill badge-default p-2">
                                        {{ array_get($split, 'transaction.status') }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="icon icon-arrow-right"></span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>

@endsection
