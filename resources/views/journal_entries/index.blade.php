@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('journal-entries.index') !!}
@endsection
@section('content')
    <div class="row" id="journal-entries">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <accounting-journal-entries
                v-bind:maxjournalid="{{ $max_journal_entry_id }}"
                v-bind:groups="{{ $groups }}"
                v-bind:funds="{{ $funds }}"
                :permissions='{!! json_encode($permissions) !!}'></accounting-journal-entries>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    @push('scripts')
        @if(session('doCreate'))
            <script>
                let doCreate = true;
            </script>
        @endif;
        <script src="{{ asset('js/accounting-journal-entries.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
