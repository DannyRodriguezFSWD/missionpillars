@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('reports.income.statement.bymonth') !!}
@endsection
@section('content')
    <div class="row" id="journal-entries">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <income-statement-by-month 
                    v-bind:funds="{{ $funds }}"
                    v-bind:tenant="{{ auth()->user()->tenant }}"></income-statement-by-month>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/accounting-reports.js') }}?t={{ time() }}"></script>
    @endpush
@endsection