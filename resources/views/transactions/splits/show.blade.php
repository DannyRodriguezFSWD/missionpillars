@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                @include('widgets.back')
            </div>
            <div class="card-body">
                @if( !strpos($action, 'recurring_transactions') !== false )
                <div class="float-right pb-2" id="floating-buttons">
                    <a id="edit-transaction" class="btn btn-primary" href="{{ route('transactions.edit', ['id' => $split->id, 'action' => 'show']) }}">
                        <span class="fa fa-edit"></span>
                        @lang('Edit')
                    </a>
                </div>
                @endif
            </div>
            <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <strong>@lang('Amount'):</strong> <span class="badge badge-pill badge-primary p-2">$ {{ array_get($split, 'amount') }}</span>
                                @if( array_get($split, 'transaction.template.is_recurring') )
                                    {{ array_get($split, 'template.billing_period') }}
                                    @lang('during') {{array_get($split, 'transaction.template.billing_cycles')}}
                                    {{ array_get($periods, array_get($split, 'transaction.template.billing_period')) }}{{ array_get($split, 'transaction.template.billing_cycles') > 1 ? 's':'' }}
                                @endif
                            </td>
                        </tr>
                        @if( array_get($split, 'transaction.template.is_recurring') )
                        <tr>
                            <td>
                                <strong>@lang('Total at the end of recurring payments'):</strong>
                                <span class="badge badge-pill badge-primary p-2">$ {{ array_get($split, 'amount') * array_get($split, 'transaction.template.billing_cycles') }}</span>
                            </td>
                        </tr>
                        @endif
                        @if( array_get($split, 'transaction.paymentOption.category') === 'cc' )
                        <tr>
                            <td>
                                <strong>@lang('Payment Option'):</strong> @lang('Credit Card'): {{ array_get($split, 'transaction.paymentOption.card_type') }} **** {{ array_get($split, 'transaction.paymentOption.last_four') }}
                            </td>
                        </tr>
                        @elseif( array_get($split, 'transaction.paymentOption.category') === 'ach' )
                        <tr>
                            <td>
                                <strong>@lang('Payment Option'):</strong> @lang('Automated Clearing House (ACH)'): **** {{ array_get($split, 'transaction.paymentOption.last_four') }}
                            </td>
                        </tr>
                        @elseif( array_get($split, 'transaction.paymentOption.category') === 'check' )
                        <tr>
                            <td>
                                <strong>@lang('Payment Option'):</strong> @lang('Check'): **** {{ array_get($split, 'transaction.paymentOption.last_four') }}
                            </td>
                        </tr>
                        @elseif(in_array(array_get($split, 'transaction.paymentOption.category'), ['cash', 'cashapp', 'venmo', 'paypal', 'facebook', 'goods', 'other', 'unknown']))
                        <tr>
                            <td>
                                <strong>@lang('Payment Option'):</strong> {{ ucfirst(array_get($split, 'transaction.paymentOption.category')) }}
                            </td>
                        </tr>
                        @endif
                        
                        @if(array_get($split, 'transaction.paymentOption.category') !== 'cash')
                        <tr>
                            <td>
                                <strong>@lang("Processor's Transaction ID"):</strong> {{ $split->transaction->payment_processor_transaction_id }}
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td>
                                <strong>@lang('Tax Deductible'):</strong>
                                {{ $split->tax_deductible ? 'YES' : 'NO' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('From'):</strong> {{ $split->transaction->contact ? $split->transaction->contact->first_name : '' }} 
                                {{ $split->transaction->contact ? $split->transaction->contact->last_name : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Email'):</strong> {{ $split->transaction->contact ? $split->transaction->contact->email_1 : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Purpose'):</strong> {{ array_get($split, 'purpose.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Fundraiser'):</strong> {{ array_get($split, 'campaign.name') }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Type'):</strong> {{ $split->type }}
                            </td>
                        </tr>
                        <!--
                        <tr>
                            <td>
                                <strong>@lang('Transaction initiated at'):</strong> {{ $split->transaction->transaction_initiated_at }}
                            </td>
                        </tr>
                        -->
                        <tr>
                            <td>
                                <strong>@lang('Time'):</strong>
                                {{ displayLocalDateTime(array_get($split, 'transaction.transaction_initiated_at')) }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>@lang('Status'):</strong> 
                                @include('transactions.includes.status-color-indicator')
                            </td>
                        </tr>
                    </tbody>
                </table>
            <div class="card-footer">&nbsp;</div>
        </div>

    </div>

</div>

<!--/.row-->
@endsection
