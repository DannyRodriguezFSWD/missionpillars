@extends('layouts.app')
@section('breadcrumbs')
    {!! Breadcrumbs::render('registers.index') !!}
@endsection
@section('content')
    <div class="row" id="accounting-registers">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <accounting-registers 
                v-bind:groups="{{ $groups }}" 
                v-bind:accounts-register="{{ $accounts_register }}" 
                v-bind:funds="{{ $funds }}"
                :permissions='{!! json_encode($permissions) !!}'></accounting-registers>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div>
    {{-- <div class="row" id="accounting-registers-table">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">&nbsp;</div>
                <accounting-registers-table v-bind:groups="{{ $groups }}" v-bind:accounts-register="{{ $accounts_register }}" v-bind:funds="{{ $funds }}" v-bind:contacts="{{ $contacts }}"></accounting-registers-table>
                <div class="card-footer">&nbsp;</div>
            </div>
        </div>
    </div> --}}
    @push('scripts')
        <script src="{{ asset('js/accounting-registers.js') }}?t={{ time() }}"></script>
    @endpush
@endsection
