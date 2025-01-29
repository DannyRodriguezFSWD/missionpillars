@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('accounting.reports.balance-sheet') !!}
@endsection
@section('content')
    <div class="row" id="journal-entries">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <balance-sheet v-bind:funds="{{ $funds }}"></balance-sheet>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/accounting-reports.js') }}?t={{ time() }}"></script>
    @endpush
@endsection