@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('pledges.show',$template) !!}
@endsection
@section('content')


<div class="card">
    <div class="card-header">
        @include('widgets.back')
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12" style="min-height: 50px;">
                <div class="float-right pb-2" id="floating-buttons">
                    @if(auth()->user()->can('pledge-update'))
                        <a id="edit-transaction" class="btn btn-primary" href="{{ route('pledges.edit', ['id' => array_get($template, 'id')]) }}">
                            <span class="fa fa-edit"></span>
                            @lang('Edit')
                        </a>
                    @endif
                    @if(auth()->user()->can('pledge-delete'))
                        <button type="button" class="btn btn-danger delete" data-form="#form-{{ array_get($template, 'id') }}" data-toggle="modal" data-target="#delete-modal">
                            <i class="fa fa-trash"></i> @lang('Delete')
                        </button>
                    @endif
                </div>
                @if(auth()->user()->can('pledge-delete'))
                    {{ Form::open( ['route' => ['pledges.destroy', $template->id], 'method' => 'DELETE', 'id' => 'form-'.array_get($template, 'id')] )  }}
                    {{ Form::hidden('uid', Crypt::encrypt(array_get($template, 'id'))) }}
                    {{ Form::close() }}
                @endif
            </div>
        </div>
        
        <table class="table">
            <tbody>
                <tr>
                    <td>
                        <strong>@lang('Amount'):</strong>
                        <span class="badge badge-pill badge-primary p-2">$ {{ array_get($template, 'splits.0.amount') }}</span>
                        @if( array_get($template, 'is_recurring') )
                        {{ array_get($template, 'billing_period') }}
                        @lang('during') {{array_get($template, 'billing_cycles')}}
                        
                        {{ array_get($periods, array_get($template, 'billing_period')) }}{{ array_get($template, 'billing_cycles') > 1 ? 's':'' }}
                        @endif
                    </td>
                </tr>
                @if( array_get($template, 'is_recurring') )
                <tr>
                    <td>
                        <strong>@lang('Total at the end of recurring payments'):</strong>
                        <span class="badge badge-pill badge-primary p-2">$ {{ number_format(array_get($template, 'splits.0.amount') * array_get($template, 'billing_cycles'), 2) }}</span>
                    </td>
                </tr>
                @endif
                <!--
                <tr>
                    <td>
                        <strong>@lang('Received'):</strong> <span class="badge badge-pill badge-success p-2">$ {{ number_format($sum, 2) }}</span>
                    </td>
                </tr>
                -->
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
                        <strong>@lang('Purpose'):</strong> {{ array_get($template, 'splits.0.purpose.name') }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>@lang('Fundraiser'):</strong> {{ array_get($template, 'splits.0.campaign.name') }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>@lang('Status'):</strong> 
                        @include('pledges.includes.status-color-indicator')
                    </td>
                </tr>
            </tbody>
        </table>
    </div>            
</div>

<div id="crm-transactions">
    <div id="crm-transactions">
        <crm-transactions 
            v-bind:endpoint="'{{ route('transactions.index') }}'"
            v-bind:display="'transactions'"
            v-bind:link_purposes_and_accounts="{{ $link_purposes_and_accounts ? 1 : 0 }}"
            v-bind:pledge_id="{{ array_get($template, 'id') }}"
            v-bind:master_id="{{ array_get($template, 'id') }}"
            v-bind:pledge_status="'{{ array_get($template, 'status') }}'"
            v-bind:create_pledge="1"
            v-bind:contact_id="{{ array_get($template, 'contact.id') }}"
            v-bind:contact_name="'{{ array_get($template, 'contact.first_name') }} {{ array_get($template, 'contact.last_name') }} ({{ array_get($template, 'contact.email_1') }})'"
            :permissions = '{!! json_encode($transaction_permissions) !!}'
            :pledge = '{!! json_encode($template) !!}'
        >
        </crm-transactions>
</div>
</div>
@include('pledges.includes.delete-modal')
@push('scripts')
<script src="{{ asset('js/crm-transactions.js') }}"></script>
@endpush
@endsection
