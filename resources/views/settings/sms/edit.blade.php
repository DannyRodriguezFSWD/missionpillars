@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('settings.sms.edit', $smsPhoneNumber) !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="text-center col-sm-12">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> @lang('Back')
                </a>
                <div>@lang('SMS settings for') <b>{{ array_get($smsPhoneNumber, 'name_and_number') }}</b></div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div id="crm-communications-viewport">
            <crm-sms-settings-notifications 
                id="{{ array_get($smsPhoneNumber, 'id') }}"
                data-type="redirect"
                data-target="{{ route('settings.sms.index') }}"
                name="{{ array_get($smsPhoneNumber, 'name') }}"
                contacts="{{ json_encode(array_get($smsPhoneNumber, 'notification_contact_list')) }}" 
                url="{{ sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain') )  }}crm/">
            </crm-sms-settings-notifications>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush

@endsection
