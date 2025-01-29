@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('journal-entries.fund-transfers') !!}
@endsection
@section('content')
    <div class="row" id="accounting-journal-entries-table">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <accounting-journal-entry-table
                v-bind:maxjournalid="{{ $max_journal_entry_id }}"
                v-bind:journalentry="{{ $all_records }}"
                v-bind:groups="{{ $groups }}"
                v-bind:funds="{{ $funds }}"
                :permissions='{!! json_encode($permissions) !!}'></accounting-journal-entry-table>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        @if(session('doCreate'))
            <script>
                let doCreate = true;
            </script>
        @endif
        <script src="{{ asset('js/accounting-fund-transfer-entries.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
