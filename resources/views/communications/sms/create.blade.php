@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('sms.create') !!}
@endsection
@section('content')
<main id="crm-communications-viewport">
    <div class="modal fade" id="notifications-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('Notifications')</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <crm-sms-settings-notifications
                            data-type="modal"
                            data-target="#notifications-modal"
                            contacts="{{ json_encode([]) }}"
                            url="{{ sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain') )  }}crm/">
                    </crm-sms-settings-notifications>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
    <crm-communications-viewport
        has-sms-phone-number="{{ $hasSMSPhoneNumber }}"
        default-sms-phone-number-id="{{ $defaultSMSPhoneNumberId }}"
        base-url="{{getBaseURL()}}"
        data-target="{{ route('settings.sms.index') }}"
        url="{{ sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain') )  }}crm/"
        sms_char_limit="{{ \App\Constants::SMS_CHAR_LIMIT }}"
    />
</main>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush

@endsection
