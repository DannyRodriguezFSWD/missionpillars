@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('subscription.index') !!}
@endsection
@section('content')

<div id="crm-billing-software-upgrade">
    <crm-billing-software-upgrade stripe-api-key="{{ env('STRIPE_KEY') }}" url="{{ route('subscription.index') }}"
    :chms_fee = "{{$chms->app_fee}}" :acct_fee = "{{$acct->app_fee}}" :contact_fee = "{{$chms->contact_fee}}"
    crmmodule="{{ ucwords(array_get($module, 'name')) }}"
    crmfeature="{{ ucwords(array_get($feature, 'display_name')) }}" accounting="{{ route('accounting.coming.soon') }}"
    :amount_unpaid = "{{ $amount_unpaid }}"
    invoice_link = "{{ route('subscription.invoices') }}"
    has_payment_option="{{ !is_null($paymentOption) }}"
    :promocodes = '{!! json_encode($promocodes) !!}'
    :discounts = '{!! json_encode($discounts) !!}'>
    </crm-billing-software-upgrade>
</div>
<p>&nbsp;</p>
@push('scripts')
<script src="https://cdn.jsdelivr.net/rangeslider.js/2.3.0/rangeslider.min.js"></script>
<script src="{{ asset('js/crm-software-billing-upgrade.js') }}?t={{ time() }}"></script>
@endpush

@endsection
