<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}
        @hasSection('title') | @yield('title')
        @endif
    </title>
    <link rel="shortcut icon" href="{{ asset('img/logo-CTG-favicon-opcion-06_favicon.png') }}" type="image/x-icon" />
    <!-- CoreUI CSS -->
    @include('includes.styles')
    
    @include('includes.header-scripts')
</head>
<body class="c-app c-legacy-theme"
      @if(env('APP_MODE') != 'production' && env('DB_DATABASE') == 'missionpillars_prod') style="border: 4px red dashed" @endif>
@include('includes.left-sidebar')
<div class="c-wrapper c-fixed-components">
    @if (!in_array(Route::currentRouteName(),['subscription.show','subscription.index']) && $trialModule && !$paymentOption)
    <div id="crm-billing-software-upgrade-modal">
        <crm-billing-software-upgrade-modal stripe-api-key="{{ env('STRIPE_KEY') }}" url="{{ route('subscription.index') }}"
        :chms_fee = "{{$chms->app_fee}}" :acct_fee = "{{$acct->app_fee}}" :contact_fee = "{{$chms->contact_fee}}"
        crmmodule="{{ ucwords(array_get($chms, 'name')) }}"
        :trial_module='{!! json_encode($trialModule) !!}'
        :amount_unpaid = "{{ $amount_unpaid }}"
        invoice_link = "{{ route('subscription.invoices') }}"
        :promocodes = '{!! json_encode($promocodes) !!}'
        :discounts = '{!! json_encode($discounts) !!}'>
        </crm-billing-software-upgrade-modal>
    </div>
    @endif
    
    @include('includes.header')
    <div class="c-body" id="app">
        <main class="c-main @isset($noPadding) p-0 @endisset">
            <div class="container-fluid @isset($noPadding) px-3 @endisset">
                @includeIf('includes.main')
            </div>
        </main>
        @if( session('START_OVER') )
            @php \App\Classes\Redirections::destroy() @endphp
        @endif

        @if (session('message'))
            @php \App\Classes\Redirections::destroy() @endphp
        @endif
    </div>
    <footer class="c-footer">
        <div><a href="{{ route('dashboard.index') }}">Mission Pillars</a>&copy; {{ date('Y') }}.</div>
    </footer>
</div>
@include('includes.scripts')
</body>
</html>