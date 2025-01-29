@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('bank-accounts.index') !!}
@endsection
@section('content')
    @push('scripts')
        <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
    @endpush
        
    @if ($bankAccountsCount === 0 && !array_get($permissions,'accounting-update'))
        <div class="row">
            <div class="col-sm-12">

                <div class="card">
                    <div class="card-header bg-warning">No Accounts / Insufficient Permissions </div>
                    <div class="card-body">
                        There are no bank accounts configured for your organization and 
                        your account has insufficient permissions to configure new accounts
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-sm-12">
                <bank-integration-acc-list 
                v-bind:list="{{ $bankInstitutions }}" 
                v-bind:funds="{{ $funds }}" 
                v-bind:unlinkedtrans="{{ $unlinkedTransactions }}" 
                v-bind:registerbalances="{{ $linkedRegisterAmounts }}" 
                v-bind:config='{{ json_encode($config) }}'
                v-bind:bank_accounts="{{ $bankAccounts }}"
                :permissions='{!! json_encode($permissions) !!}'
                accountsroute="{{ route('accounts.index') }}"
                ></bank-integration-acc-list>
            </div>
        </div>
    @endif
    
    @include('bank_accounts.includes.plaidnoaccountserrormodal')
    
    @push('scripts')
        <script src="{{ asset('js/accounting-bank-integration-acc-list.js') }}?t={{ time() }}"></script>
        <script type="text/javascript">
        
        $('#link-btn').on('click', function() {
            customAjax({
                url: '{{ route('bank.link.create') }}',
                success: function (response) {
                    var handler = Plaid.create({
                        token: response.linkToken,
                        onSuccess: function(public_token, metadata) {
                            $('#overlay').fadeIn();

                            $.post('bank_authorization_callback', {
                                public_token: public_token,
                                metadata: metadata
                            }, function(data) {
                                if (data.success) {
                                    window.location.reload();
                                } else if (data.error && data.error_code === 'NO_ACCOUNTS') {
                                    $('#plaidNoAccountsErrorModal').modal('show');
                                    $('#overlay').fadeOut();
                                } else {
                                    Swal.fire(data.error_code, data.error_message, 'error');
                                    $('#overlay').fadeOut();
                                }
                            });
                        },
                        onExit: function(error, metadata) {
                            if (!error) return
                            $.post('/crm/ajax/errors', {
                                event: 'Plaid_v2_Error', // 
                                request: metadata,
                                response: error
                            },
                            function(resp) { 
                                console.log(resp);
                            });
                        }
                    });

                    handler.open();
                }
            });
        });
        
        $('#plaidTryAgain').click(function () {
            $('#link-btn').trigger('click');
        });
        </script>
    @endpush
@endsection
