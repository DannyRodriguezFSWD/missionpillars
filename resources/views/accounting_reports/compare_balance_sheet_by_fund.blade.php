@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('accounting.reports.compare-balance-sheet-by-fund') !!}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <compare-balance-sheet-by-fund v-bind:funds="{{ $funds }}"></compare-balance-sheet-by-fund>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/accounting-reports.js') }}?t={{ time() }}"></script>
    @endpush
@endsection