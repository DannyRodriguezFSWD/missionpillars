@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('crmreports.index') !!}
@endsection
@section('content')
    
    <div id="crm-reports-viewport">
        <crm-reports-components 
        reports-list="show" 
        base="{{ url('crm') }}"
        in_tags="{{ $in_tags }}"
        out_tags="{{ $out_tags }}"
        v-bind:amount_ranges="{{ $amount_ranges }}"
        ></crm-reports-components>
    </div>
    
@push('scripts')
<script src="{{ asset('js/crm-reports-components.js') }}?t={{ time() }}"></script>
@endpush
@endsection