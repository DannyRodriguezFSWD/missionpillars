@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('subscription.show',request()->segment(4)) !!}
@endsection
@section('content')

<div id="crm-billing-software-upgrade">
    <crm-billing-payment-options stripe-api-key="{{ env('STRIPE_KEY') }}" url="{{ route('subscription.index') }}">
    </crm-billing-payment-options>
</div>

@push('scripts')
<script src="{{ asset('js/crm-software-billing-upgrade.js') }}?t={{ time() }}"></script>
@endpush

@endsection
