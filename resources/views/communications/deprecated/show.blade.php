{{-- DEPRECATED see ../show.php (if still exists )--}}
@extends('layouts.app')

@section('content')
<main id="crm-communications-viewport">
    <crm-communications-viewport
        sms-phone-number="{{ $SMSPhoneNumber }}"
        base-url="{{getBaseURL()}}"
        current-phone="{{ array_get(auth()->user(), 'notify_to_phone') }}"
        current-email="{{ array_get(auth()->user(), 'notify_to_email') }}"
    />
</main>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush

@endsection
