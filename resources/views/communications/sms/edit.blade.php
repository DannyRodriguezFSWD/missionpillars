@extends('layouts.app')

@section('content')
<main id="crm-communications-viewport">
    <crm-communications-viewport
        has-sms-phone-number="{{ $hasSMSPhoneNumber }}"
        default-sms-phone-number-id="{{ $defaultSMSPhoneNumberId }}"
        base-url="{{getBaseURL()}}"
        :sms='{!! json_encode($sms) !!}'
    />
</main>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush

@endsection
