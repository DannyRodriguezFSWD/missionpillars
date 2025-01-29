@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('accounting.reports.income-statement') !!}
@endsection
@section('content')
    <div class="row" id="journal-entries">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <income-statement 
                    v-bind:funds="{{ $funds }}"
                    v-bind:tenant="{{ auth()->user()->tenant }}"></income-statement>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/accounting-reports.js') }}?t={{ time() }}"></script>
    @endpush
@endsection