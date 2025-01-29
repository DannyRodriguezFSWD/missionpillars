@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            @if (auth()->user()->can('contact-update'))
            <div class="card-header">
                @include('people.contacts.includes.card-header')
            </div>
            @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="lead bg-faded">@lang('Transactions')</p>
                        @if (auth()->user()->can('transaction-update'))
                        <div class="text-right mb-2">
                            <button type="button" class="btn btn-primary" id="email_statement_modal_btn">
                                <i class="fa fa-envelope-o"></i> @lang('Email Statement')
                            </button>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statement-modal">
                                <i class="fa fa-print"></i> @lang('Print Statement')
                            </button>
                        </div>
                        @endif
                        <div id="crm-transactions">
                            <crm-transactions
                                :endpoint="'{{ route('transactions.index') }}'"
                                :display="'transactions'"
                                :link_purposes_and_accounts="{{ $link_purposes_and_accounts ? 1 : 0 }}"
                                :contact_id="{{ $contact->id }}"
                                contact_name="{{ $contact->first_name . " " . $contact->last_name }}"
                                
                                :pledge_id="0"
                                :permissions='{!! json_encode($transaction_permissions) !!}'
                            >
                            </crm-transactions>
                        </div>
                        @includeIf('shared.transactions.normal.index-old')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/.col-->
</div>
<!--/.row-->

@includeIf('people.contacts.includes.statement-modal')
@includeIf('people.contacts.includes.email-statement-modal')
@push('scripts')
<script>
    var contentTempaltes = {!! $templates->keyBy('id')->toJson() !!};
</script>
<script src="{{ asset('js/crm-transactions.js') }}"></script>
<script>
    $('#email_statement_modal_btn').click(function (){
        $('#email-statement-modal').modal('show')
    })
</script>
@endpush

@endsection
