@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('settings.sms.create') !!}
@endsection
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="text-center col-sm-12">
                <a href="javascript:history.back()" class="pull-left">
                    <span class="fa fa-chevron-left"></span> @lang('Back')
                </a>
                <div>@lang('SMS Settings')</div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div id="crm-communications-viewport">
            <crm-buy-phone-number 
                data-type="redirect"
                data-target="{{ route('settings.sms.index') }}"
                stand-alone-component="true" 
                url="{{ sprintf(env('APP_DOMAIN'), array_get(auth()->user(), 'tenant.subdomain') )  }}crm/">
            </crm-buy-phone-number>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/crm-communications-components.js') }}?t={{ time() }}"></script>
@endpush

@endsection